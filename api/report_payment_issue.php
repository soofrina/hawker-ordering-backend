<?php
// ════════════════════════════════════════════════════════════════
// REPORT PAYMENT ISSUE  (POST, JSON body)
// A hawker escalates "customer says they paid but I see no payment"
// up to the superadmin. Creates one row in payment_issue (status 'open').
//
//   { "order_id": 2, "note": "customer says PayNow paid, nothing received" }
//
// stall_id and reported_by (the hawker) are looked up from the order's
// stall, so the front-end only has to send the order_id.
//
// (NOTE: no login yet — once your authentication table + login flow is
//  wired up, this should confirm the caller actually owns this stall
//  before allowing the report. Same open item as update_order_status.php.)
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

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON body']);
    exit;
}
$orderId = $input['order_id'] ?? null;
$note    = trim((string)($input['note'] ?? ''));
if (!$orderId) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Need order_id']);
    exit;
}

try {
    $pdo = db();

    // 1. The order must exist — also gives us its stall.
    $o = $pdo->prepare("SELECT order_id, stall_id FROM `order` WHERE order_id = ?");
    $o->execute([$orderId]);
    $order = $o->fetch();
    if (!$order) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Order not found']);
        exit;
    }
    $stallId = (int) $order['stall_id'];

    // 2. The hawker = the stall's owner (stand-in until login exists).
    $s = $pdo->prepare("SELECT stall_owner_id FROM stall WHERE stall_id = ?");
    $s->execute([$stallId]);
    $stall = $s->fetch();
    if (!$stall || $stall['stall_owner_id'] === null) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'error' => 'Stall has no owner on record']);
        exit;
    }
    $reportedBy = (int) $stall['stall_owner_id'];

    // 3. Don't open duplicates — if this order already has an open issue,
    //    return that one instead of stacking another.
    $dup = $pdo->prepare(
        "SELECT issue_id FROM payment_issue
         WHERE order_id = ? AND issue_status = 'open'
         LIMIT 1"
    );
    $dup->execute([$orderId]);
    if ($existing = $dup->fetch()) {
        echo json_encode([
            'ok'       => true,
            'issue_id' => (int) $existing['issue_id'],
            'message'  => 'An open issue already exists for this order.',
        ]);
        exit;
    }

    // 4. Create the issue (status defaults to 'open' in the table).
    $ins = $pdo->prepare(
        "INSERT INTO payment_issue (order_id, stall_id, reported_by, note)
         VALUES (?, ?, ?, ?)"
    );
    $ins->execute([$orderId, $stallId, $reportedBy, $note]);

    echo json_encode([
        'ok'           => true,
        'issue_id'     => (int) $pdo->lastInsertId(),
        'order_id'     => (int) $orderId,
        'stall_id'     => $stallId,
        'reported_by'  => $reportedBy,
        'issue_status' => 'open',
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
