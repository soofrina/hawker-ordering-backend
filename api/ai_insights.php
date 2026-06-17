<?php
// ════════════════════════════════════════════════════════════════
// AI INSIGHTS (GET) — OpenAI version
//   api/ai_insights.php?stall_id=1&year=2026&month=6
//
// Pulls this stall's monthly stats (totals, top sellers, meal-period
// split) and asks GPT for a short, plain-English comment grounded in
// the real numbers. The API key lives only on the server — read from
// the OPENAI_API_KEY environment variable (set in Render's Environment
// tab for production; set locally via ai_config.php, which is
// gitignored and never committed).
// ════════════════════════════════════════════════════════════════
require __DIR__ . '/../db.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Local-only: ai_config.php sets the env var for XAMPP testing.
// On Render this file won't exist — the env var is set in the dashboard instead.
if (file_exists(__DIR__ . '/../ai_config.php')) {
    require __DIR__ . '/../ai_config.php';
}

$apiKey = getenv('OPENAI_API_KEY');
if (!$apiKey) {
    echo json_encode([
        'ok' => false,
        'error' => 'AI insights are not set up yet (no API key configured on the server).',
    ]);
    exit;
}

$stallId = $_GET['stall_id'] ?? null;
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');
if (!$stallId) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Need stall_id']);
    exit;
}

try {
    $pdo = db();

    // Same lightweight stats monthly_sales.php computes — kept independent
    // here so this endpoint has no dependency on that file.
    $summary = $pdo->prepare(
        "SELECT COUNT(*) as total_orders, SUM(total_amount) as total_revenue,
                SUM(CASE WHEN order_status='collected' THEN 1 ELSE 0 END) as collected,
                SUM(CASE WHEN order_status='cancelled' THEN 1 ELSE 0 END) as cancelled
         FROM `order`
         WHERE stall_id = ? AND YEAR(created_at) = ? AND MONTH(created_at) = ?"
    );
    $summary->execute([$stallId, $year, $month]);
    $stats = $summary->fetch() ?: [];
    $totalOrders = (int)($stats['total_orders'] ?? 0);

    if ($totalOrders === 0) {
        // Nothing to analyse yet — skip the API call entirely (saves a call & cost).
        echo json_encode([
            'ok' => true,
            'insight' => "No sales recorded yet this month, so there isn't enough to comment on. Check back once a few orders have come in!",
        ]);
        exit;
    }

    $items = $pdo->prepare(
        "SELECT m.item_name, SUM(oi.quantity) as total_qty, SUM(oi.item_price) as total_revenue
         FROM order_item oi
         JOIN menu_item m ON m.item_id = oi.item_id
         JOIN `order` o ON o.order_id = oi.order_id
         WHERE o.stall_id = ? AND YEAR(o.created_at) = ? AND MONTH(o.created_at) = ?
         GROUP BY m.item_id, m.item_name
         ORDER BY total_revenue DESC
         LIMIT 5"
    );
    $items->execute([$stallId, $year, $month]);
    $byItem = $items->fetchAll();

    $meals = $pdo->prepare(
        "SELECT
            CASE
              WHEN HOUR(DATE_ADD(created_at, INTERVAL 8 HOUR)) < 11 THEN 'breakfast'
              WHEN HOUR(DATE_ADD(created_at, INTERVAL 8 HOUR)) < 17 THEN 'lunch'
              ELSE 'dinner'
            END AS meal,
            COUNT(*) as orders, SUM(total_amount) as revenue
         FROM `order`
         WHERE stall_id = ? AND YEAR(created_at) = ? AND MONTH(created_at) = ?
         GROUP BY meal"
    );
    $meals->execute([$stallId, $year, $month]);
    $byMealRaw = $meals->fetchAll();
    $byMeal = ['breakfast' => 0, 'lunch' => 0, 'dinner' => 0];
    $byMealRevenue = ['breakfast' => 0, 'lunch' => 0, 'dinner' => 0];
    foreach ($byMealRaw as $r) {
        if (isset($byMeal[$r['meal']])) {
            $byMeal[$r['meal']] = (int)$r['orders'];
            $byMealRevenue[$r['meal']] = (float)$r['revenue'];
        }
    }

    $payloadForModel = [
        'month' => date('F', mktime(0, 0, 0, (int)$month, 1)) . ' ' . $year,
        'total_orders' => $totalOrders,
        'total_revenue_sgd' => round((float)($stats['total_revenue'] ?? 0), 2),
        'cancelled_orders' => (int)($stats['cancelled'] ?? 0),
        'top_sellers' => array_map(fn($i) => [
            'item' => $i['item_name'],
            'qty_sold' => (int)$i['total_qty'],
            'revenue_sgd' => round((float)$i['total_revenue'], 2),
        ], $byItem),
        'orders_by_meal_period' => [
            'breakfast' => ['orders' => $byMeal['breakfast'], 'revenue_sgd' => round($byMealRevenue['breakfast'], 2)],
            'lunch'     => ['orders' => $byMeal['lunch'],     'revenue_sgd' => round($byMealRevenue['lunch'], 2)],
            'dinner'    => ['orders' => $byMeal['dinner'],    'revenue_sgd' => round($byMealRevenue['dinner'], 2)],
        ],
    ];

    $systemPrompt =
        "You are a friendly, practical business advisor for a small hawker food stall in Singapore. " .
        "You will be given that stall's real sales data for one month as JSON. " .
        "Write 3 to 4 short sentences of plain-English commentary: name the standout pattern " .
        "(best seller, busiest meal period, or a slow period), use the actual numbers given, and end with " .
        "one concrete, actionable suggestion. Use SGD with a \$ sign. Plain flowing sentences only — " .
        "no markdown, no headers, no bullet points, no emoji. Warm and practical, not corporate.";

    $requestBody = [
        'model' => 'gpt-5.4-mini',
        'max_completion_tokens' => 300,
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => "Here is this stall's sales data:\n" . json_encode($payloadForModel)],
        ],
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($requestBody),
        CURLOPT_HTTPHEADER => [
            'content-type: application/json',
            'authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_TIMEOUT => 30,
    ]);
    $raw = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErr) {
        echo json_encode(['ok' => false, 'error' => 'Could not reach the AI service: ' . $curlErr]);
        exit;
    }

    $data = json_decode($raw, true);

    if ($httpCode !== 200) {
        $msg = $data['error']['message'] ?? ('HTTP ' . $httpCode);
        echo json_encode(['ok' => false, 'error' => 'AI service error: ' . $msg]);
        exit;
    }

    $text = trim($data['choices'][0]['message']['content'] ?? '');

    if ($text === '') {
        echo json_encode(['ok' => false, 'error' => 'The AI service returned an empty response.']);
        exit;
    }

    echo json_encode(['ok' => true, 'insight' => $text]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
