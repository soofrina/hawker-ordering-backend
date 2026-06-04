<?php
// ════════════════════════════════════════════════════════════════
// DAILY SALES REPORT (GET)
//   api/daily_sales.php?stall_id=1&date=2026-06-04
// Returns: total orders, revenue, breakdown by status, breakdown by item.
// ════════════════════════════════════════════════════════════════
require __DIR__ . '/../db.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$stallId = $_GET['stall_id'] ?? null;
$date = $_GET['date'] ?? date('Y-m-d');  // default today
if (!$stallId) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Need stall_id']);
    exit;
}

try {
    $pdo = db();
    
    // Overall totals for the day.
    $summary = $pdo->prepare(
        "SELECT 
            COUNT(*) as total_orders,
            SUM(total_amount) as total_revenue,
            SUM(CASE WHEN order_status='collected' THEN 1 ELSE 0 END) as collected,
            SUM(CASE WHEN order_status='cancelled' THEN 1 ELSE 0 END) as cancelled,
            SUM(CASE WHEN order_status IN ('received','preparing','ready') THEN 1 ELSE 0 END) as pending
         FROM \`order\`
         WHERE stall_id = ? AND DATE(created_at) = ?"
    );
    $summary->execute([$stallId, $date]);
    $stats = $summary->fetch() ?: [];
    
    // Breakdown by item.
    $items = $pdo->prepare(
        "SELECT 
            m.item_id,
            m.item_name,
            SUM(oi.quantity) as total_qty,
            SUM(oi.item_price) as total_revenue
         FROM order_item oi
         JOIN menu_item m ON m.item_id = oi.item_id
         JOIN \`order\` o ON o.order_id = oi.order_id
         WHERE o.stall_id = ? AND DATE(o.created_at) = ?
         GROUP BY m.item_id, m.item_name
         ORDER BY total_revenue DESC"
    );
    $items->execute([$stallId, $date]);
    $byItem = $items->fetchAll();
    
    echo json_encode([
        'ok' => true,
        'stall_id' => (int)$stallId,
        'date' => $date,
        'summary' => [
            'total_orders' => (int)($stats['total_orders'] ?? 0),
            'total_revenue' => (float)($stats['total_revenue'] ?? 0),
            'collected' => (int)($stats['collected'] ?? 0),
            'pending' => (int)($stats['pending'] ?? 0),
            'cancelled' => (int)($stats['cancelled'] ?? 0),
        ],
        'by_item' => $byItem,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
