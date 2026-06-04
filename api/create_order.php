<?php
// ════════════════════════════════════════════════════════════════
// CREATE ORDER  (POST, JSON body)
// Expects JSON like:
// {
//   "user_id": 1,
//   "stall_id": 1,
//   "order_type": "takeaway",          // or "dine-in"
//   "items": [
//     { "item_id": 1, "quantity": 2, "special_request": "no chilli" },
//     { "item_id": 2, "quantity": 1, "special_request": "" }
//   ]
// }
// Returns the new order_id, queue_number and verification_code.
// ════════════════════════════════════════════════════════════════
require __DIR__ . '/../db.php';

// CORS — lets your PWA call this from a different port during development.
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

// --- Read and validate the cart ---
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON body']);
    exit;
}
$userId    = $input['user_id']    ?? null;
$stallId   = $input['stall_id']   ?? null;
$orderType = $input['order_type'] ?? 'takeaway';
$items     = $input['items']      ?? [];

if (!$userId || !$stallId || !is_array($items) || count($items) === 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Need user_id, stall_id and at least one item']);
    exit;
}

$pdo = db();
try {
    $pdo->beginTransaction();

    // Lock THIS stall's row. Two orders for the same stall now take turns here,
    // so they can't grab the same queue number. Other stalls are unaffected.
    $stall = $pdo->prepare("SELECT stall_id, open_status FROM `stall` WHERE stall_id = ? FOR UPDATE");
    $stall->execute([$stallId]);
    $stallRow = $stall->fetch();
    if (!$stallRow)                          throw new RuntimeException('Stall not found');
    if ((int)$stallRow['open_status'] === 0) throw new RuntimeException('Stall is currently closed');

    // Re-price every item FROM THE DATABASE (never trust prices sent by the browser)
    // and check it's available and actually on this stall's menu.
    $look = $pdo->prepare(
        "SELECT item_id, item_name, base_price, availability
         FROM menu_item WHERE item_id = ? AND stall_id = ?"
    );
    $total = 0;
    $lines = [];
    foreach ($items as $it) {
        $itemId = $it['item_id'] ?? null;
        $qty    = (int)($it['quantity'] ?? 1);
        $note   = trim((string)($it['special_request'] ?? ''));
        if (!$itemId || $qty < 1) throw new RuntimeException('Each item needs item_id and quantity >= 1');

        $look->execute([$itemId, $stallId]);
        $menu = $look->fetch();
        if (!$menu)                          throw new RuntimeException("Item {$itemId} is not on this stall's menu");
        if ((int)$menu['availability'] === 0) throw new RuntimeException("'{$menu['item_name']}' is sold out");

        $linePrice = (float)$menu['base_price'] * $qty;
        $total += $linePrice;
        $lines[] = ['item_id' => $itemId, 'qty' => $qty, 'note' => $note, 'price' => $linePrice];
    }

    // Next queue number for this stall, today (resets daily). Safe under the stall lock.
    $q = $pdo->prepare(
        "SELECT COALESCE(MAX(queue_number), 0) + 1
         FROM `order` WHERE stall_id = ? AND DATE(created_at) = CURDATE()"
    );
    $q->execute([$stallId]);
    $queueNumber = (int)$q->fetchColumn();

    // Short collection code shown to the customer.
    $verifyCode = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);

    // Write the order, then its lines.
    $insOrder = $pdo->prepare(
        "INSERT INTO `order` (user_id, stall_id, order_type, total_amount, order_status, queue_number, verification_code)
         VALUES (?, ?, ?, ?, 'received', ?, ?)"
    );
    $insOrder->execute([$userId, $stallId, $orderType, $total, $queueNumber, $verifyCode]);
    $orderId = (int)$pdo->lastInsertId();

    $insItem = $pdo->prepare(
        "INSERT INTO order_item (order_id, item_id, quantity, special_request, item_price)
         VALUES (?, ?, ?, ?, ?)"
    );
    foreach ($lines as $l) {
        $insItem->execute([$orderId, $l['item_id'], $l['qty'], $l['note'], $l['price']]);
    }

    $pdo->commit();

    echo json_encode([
        'ok'                => true,
        'order_id'          => $orderId,
        'queue_number'      => $queueNumber,
        'verification_code' => $verifyCode,
        'total_amount'      => number_format($total, 2, '.', ''),
        'status'            => 'received',
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();   // all-or-nothing: a failed order writes nothing
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
