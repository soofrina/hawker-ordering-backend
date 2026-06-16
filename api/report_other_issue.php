<?php
// ════════════════════════════════════════════════════════════════
// REPORT OTHER ISSUE (POST, JSON body)
// A hawker raises a general issue (not a payment dispute) to the
// superadmin. Creates a row in support_ticket (status 'open').
//
//   { "stall_id": 1, "subject": "Menu photo won't load", "message": "..." }
//
// reported_by (the hawker) is looked up from the stall's owner.
// (NOTE: no login yet — once auth exists, derive stall_id from the
//  logged-in hawker rather than trusting the request.)
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
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}
$stallId = $input['stall_id'] ?? null;
$subject = trim($input['subject'] ?? '');
$message = trim($input['message'] ?? '');
if (!$stallId || $subject === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Need stall_id and subject']);
    exit;
}

try {
    $pdo = db();

    // The hawker = the stall's owner (stand-in until login exists).
    $s = $pdo->prepare("SELECT stall_owner_id FROM stall WHERE stall_id = ?");
    $s->execute([$stallId]);
    $stall = $s->fetch();
    if (!$stall) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Stall not found']);
        exit;
    }
    $reportedBy = $stall['stall_owner_id'] ?? null;

    $ins = $pdo->prepare(
        "INSERT INTO support_ticket (stall_id, reported_by, subject, message)
         VALUES (?, ?, ?, ?)"
    );
    $ins->execute([$stallId, $reportedBy, $subject, $message]);

    echo json_encode([
        'ok'            => true,
        'ticket_id'     => (int)$pdo->lastInsertId(),
        'ticket_status' => 'open',
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
