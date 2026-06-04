<?php
// ════════════════════════════════════════════════════════════════
// UPDATE ORDER STATUS  (POST, JSON body)
// The hawker dashboard calls this to move an order forward.
//
// Advance one step:   { "order_id": 2, "action": "advance" }
// Set explicitly:     { "order_id": 2, "status": "ready" }   // or "cancelled"
//
// Flow: received -> preparing -> ready -> collected
// (NOTE: no login yet — once your authentication table is wired up,
//  this endpoint should be restricted to the stall's owner.)
// ════════════════════════════════════════════════════════════════
require __DIR__ . '/../db.php';
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Use POST']);
    exit;
}

$flow = ['received', 'preparing', 'ready', 'collected'];

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON body']);
    exit;
}
$orderId = $input['order_id'] ?? null;
$action  = $input['action']   ?? null;   // "advance"
$target  = $input['status']   ?? null;   // explicit status
if (!$orderId) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Need order_id']);
    exit;
}

$pdo = db();
try {
    $pdo->beginTransaction();

    // Lock the order row so two hawker taps can't fight over the status.
    $o = $pdo->prepare("SELECT order_status FROM `order` WHERE order_id = ? FOR UPDATE");
    $o->execute([$orderId]);
    $row = $o->fetch();
    if (!$row) throw new RuntimeException('Order not found');
    $current = $row['order_status'];

    if ($action === 'advance') {
        $i = array_search($current, $flow, true);
        if ($i === false)               throw new RuntimeException("Cannot advance from '{$current}'");
        if ($i === count($flow) - 1)    throw new RuntimeException("Order is already '{$current}'");
        $newStatus = $flow[$i + 1];
    } elseif ($target !== null) {
        $allowed = array_merge($flow, ['cancelled']);
        if (!in_array($target, $allowed, true)) throw new RuntimeException("Invalid status '{$target}'");
        $newStatus = $target;
    } else {
        throw new RuntimeException("Send either action:'advance' or status:'...'");
    }

    $u = $pdo->prepare("UPDATE `order` SET order_status = ? WHERE order_id = ?");
    $u->execute([$newStatus, $orderId]);
    $pdo->commit();

    echo json_encode([
        'ok'              => true,
        'order_id'        => (int)$orderId,
        'previous_status' => $current,
        'status'          => $newStatus,
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
