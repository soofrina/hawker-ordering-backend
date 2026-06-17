<?php
// ════════════════════════════════════════════════════════════════
// MONTHLY SALES REPORT (GET)
//   api/monthly_sales.php?stall_id=1&year=2026&month=6
// Returns: total orders, revenue, daily breakdown, breakdown by item,
//          and a breakfast/lunch/dinner breakdown (Singapore time).
// ════════════════════════════════════════════════════════════════
require __DIR__ . '/../db.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

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

    // Overall totals for the month.
    $summary = $pdo->prepare(
        "SELECT 
            COUNT(*) as total_orders,
            SUM(total_amount) as total_revenue,
            SUM(CASE WHEN order_status='collected' THEN 1 ELSE 0 END) as collected,
            SUM(CASE WHEN order_status='cancelled' THEN 1 ELSE 0 END) as cancelled
         FROM `order`
         WHERE stall_id = ? AND YEAR(created_at) = ? AND MONTH(created_at) = ?"
    );
    $summary->execute([$stallId, $year, $month]);
    $stats = $summary->fetch() ?: [];

    // Daily breakdown for the month.
    $daily = $pdo->prepare(
        "SELECT 
            DATE(created_at) as day,
            COUNT(*) as orders,
            SUM(total_amount) as revenue
         FROM `order`
         WHERE stall_id = ? AND YEAR(created_at) = ? AND MONTH(created_at) = ?
         GROUP BY DATE(created_at)
         ORDER BY day DESC"
    );
    $daily->execute([$stallId, $year, $month]);
    $byDay = $daily->fetchAll();

    // Breakdown by item for the month.
    $items = $pdo->prepare(
        "SELECT 
            m.item_id,
            m.item_name,
            SUM(oi.quantity) as total_qty,
            SUM(oi.item_price) as total_revenue
         FROM order_item oi
         JOIN menu_item m ON m.item_id = oi.item_id
         JOIN `order` o ON o.order_id = oi.order_id
         WHERE o.stall_id = ? AND YEAR(o.created_at) = ? AND MONTH(o.created_at) = ?
         GROUP BY m.item_id, m.item_name
         ORDER BY total_revenue DESC"
    );
    $items->execute([$stallId, $year, $month]);
    $byItem = $items->fetchAll();

    // Breakdown by meal period (Singapore time).
    // created_at is stored in UTC, so we add 8 hours before reading the hour:
    //   breakfast = before 11:00, lunch = 11:00-16:59, dinner = 17:00 onward.
    $meals = $pdo->prepare(
        "SELECT 
            CASE
              WHEN HOUR(DATE_ADD(created_at, INTERVAL 8 HOUR)) < 11 THEN 'breakfast'
              WHEN HOUR(DATE_ADD(created_at, INTERVAL 8 HOUR)) < 17 THEN 'lunch'
              ELSE 'dinner'
            END AS meal,
            COUNT(*) as orders,
            SUM(total_amount) as revenue
         FROM `order`
         WHERE stall_id = ? AND YEAR(created_at) = ? AND MONTH(created_at) = ?
         GROUP BY meal"
    );
    $meals->execute([$stallId, $year, $month]);
    $byMealRaw = $meals->fetchAll();

    // Normalise so all three periods are always present (zeros if none).
    $byMeal = [
        'breakfast' => ['orders' => 0, 'revenue' => 0.0],
        'lunch'     => ['orders' => 0, 'revenue' => 0.0],
        'dinner'    => ['orders' => 0, 'revenue' => 0.0],
    ];
    foreach ($byMealRaw as $r) {
        $k = $r['meal'];
        if (isset($byMeal[$k])) {
            $byMeal[$k] = ['orders' => (int)$r['orders'], 'revenue' => (float)$r['revenue']];
        }
    }

    echo json_encode([
        'ok' => true,
        'stall_id' => (int)$stallId,
        'year' => (int)$year,
        'month' => (int)$month,
        'summary' => [
            'total_orders' => (int)($stats['total_orders'] ?? 0),
            'total_revenue' => (float)($stats['total_revenue'] ?? 0),
            'collected' => (int)($stats['collected'] ?? 0),
            'cancelled' => (int)($stats['cancelled'] ?? 0),
        ],
        'by_day' => $byDay,
        'by_item' => $byItem,
        'by_meal' => $byMeal,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
