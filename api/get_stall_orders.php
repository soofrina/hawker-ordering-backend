<?php
// ════════════════════════════════════════════════════════════════
// GET STALL ORDERS (GET) — the hawker's live order board.
//   api/get_stall_orders.php?stall_id=1
// Returns active orders (received, preparing, ready) for the stall,
// oldest first (so you cook in the order they came in), each with
// its line items.
//   Optional: &all=1  to include collected/cancelled too.
// ════════════════════════════════════════════════════════════════
require __DIR__ . '/../db.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$stallId = $_GET['stall_id'] ?? null;
$all     = !empty($_GET['all']);
if (!$stallId) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Need stall_id']);
    exit;
}

try {
    $pdo = db();

    $sql = "SELECT order_id, queue_number, verification_code, order_type,
                   total_amount, order_status, created_at
            FROM `order`
            WHERE stall_id = ?";
    $params = [$stallId];
    if (!$all) {
        $sql .= " AND order_status IN ('received','preparing','ready')";
    }
    $sql .= " ORDER BY created_at ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();

    // Attach line items to each order.
    $itemStmt = $pdo->prepare(
        "SELECT m.item_name, oi.quantity, oi.special_request, oi.item_price
         FROM order_item oi
         JOIN menu_item m ON m.item_id = oi.item_id
         WHERE oi.order_id = ?"
    );
    foreach ($orders as &$o) {
        $itemStmt->execute([$o['order_id']]);
        $o['items'] = $itemStmt->fetchAll();
    }
    unset($o);

    echo json_encode(['ok' => true, 'stall_id' => (int)$stallId, 'orders' => $orders]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
