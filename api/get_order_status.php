<?php
// ════════════════════════════════════════════════════════════════
// GET ORDER STATUS  (GET)
// The customer's "Live status" screen calls this to see where their
// order is. Requires the verification_code as a light ownership check
// so people can't snoop on other orders by guessing IDs.
//
//   api/get_order_status.php?order_id=2&code=9846
// ════════════════════════════════════════════════════════════════
require __DIR__ . '/../db.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$orderId = $_GET['order_id'] ?? null;
$code    = $_GET['code']     ?? null;
if (!$orderId) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Need order_id']);
    exit;
}

try {
    $pdo = db();
    $o = $pdo->prepare(
        "SELECT order_id, stall_id, order_type, total_amount, order_status,
                queue_number, verification_code, created_at
         FROM `order` WHERE order_id = ?"
    );
    $o->execute([$orderId]);
    $order = $o->fetch();
    if (!$order) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Order not found']);
        exit;
    }

    // Light guard: must know this order's verification code.
    if ($code === null || !hash_equals($order['verification_code'], (string)$code)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Wrong or missing verification code']);
        exit;
    }

    $it = $pdo->prepare(
        "SELECT oi.item_id, m.item_name, oi.quantity, oi.special_request, oi.item_price
         FROM order_item oi
         JOIN menu_item m ON m.item_id = oi.item_id
         WHERE oi.order_id = ?"
    );
    $it->execute([$orderId]);

    echo json_encode([
        'ok'           => true,
        'order_id'     => (int)$order['order_id'],
        'stall_id'     => (int)$order['stall_id'],
        'queue_number' => (int)$order['queue_number'],
        'status'       => $order['order_status'],   // received / preparing / ready / collected / cancelled
        'order_type'   => $order['order_type'],
        'total_amount' => $order['total_amount'],
        'created_at'   => $order['created_at'],
        'items'        => $it->fetchAll(),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
