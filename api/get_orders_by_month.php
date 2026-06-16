<?php
// ════════════════════════════════════════════════════════════════
// GET ORDERS BY MONTH (GET) — past-orders log for the dashboard.
//   api/get_orders_by_month.php?stall_id=1&year=2026&month=6
// Returns every order for that stall in that month (all statuses),
// newest first, each with a line-item count.
// ════════════════════════════════════════════════════════════════
require __DIR__ . '/../db.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$stallId = $_GET['stall_id'] ?? null;
$year    = $_GET['year']  ?? date('Y');
$month   = $_GET['month'] ?? date('m');
if (!$stallId) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Need stall_id']);
    exit;
}

try {
    $pdo = db();
    $stmt = $pdo->prepare(
        "SELECT o.order_id, o.queue_number, o.verification_code, o.order_type,
                o.total_amount, o.order_status, o.created_at,
                COUNT(oi.order_id) AS item_count
         FROM `order` o
         LEFT JOIN order_item oi ON oi.order_id = o.order_id
         WHERE o.stall_id = ? AND YEAR(o.created_at) = ? AND MONTH(o.created_at) = ?
         GROUP BY o.order_id
         ORDER BY o.created_at DESC"
    );
    $stmt->execute([$stallId, $year, $month]);
    $orders = $stmt->fetchAll();

    echo json_encode([
        'ok' => true,
        'stall_id' => (int)$stallId,
        'year' => (int)$year,
        'month' => (int)$month,
        'orders' => $orders,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
