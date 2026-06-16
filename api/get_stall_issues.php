<?php
// ════════════════════════════════════════════════════════════════
// GET STALL ISSUES (GET) — every issue a stall has raised:
// payment disputes + general support tickets, merged, newest first.
//   api/get_stall_issues.php?stall_id=1
// ════════════════════════════════════════════════════════════════
require __DIR__ . '/../db.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$stallId = $_GET['stall_id'] ?? null;
if (!$stallId) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Need stall_id']);
    exit;
}

try {
    $pdo = db();

    // Payment issues (join the order to show the customer's code).
    $pi = $pdo->prepare(
        "SELECT pi.issue_id, o.verification_code, pi.note, pi.issue_status, pi.created_at
         FROM payment_issue pi
         LEFT JOIN `order` o ON o.order_id = pi.order_id
         WHERE pi.stall_id = ?
         ORDER BY pi.created_at DESC"
    );
    $pi->execute([$stallId]);
    $pays = $pi->fetchAll();

    // General support tickets.
    $st = $pdo->prepare(
        "SELECT ticket_id, subject, message, ticket_status, created_at
         FROM support_ticket WHERE stall_id = ? ORDER BY created_at DESC"
    );
    $st->execute([$stallId]);
    $tix = $st->fetchAll();

    $issues = [];
    foreach ($pays as $p) {
        $issues[] = [
            'type' => 'payment',
            'id' => (int)$p['issue_id'],
            'title' => 'Payment · order #' . ($p['verification_code'] ?? '?'),
            'detail' => $p['note'],
            'status' => $p['issue_status'],
            'created_at' => $p['created_at'],
        ];
    }
    foreach ($tix as $t) {
        $issues[] = [
            'type' => 'other',
            'id' => (int)$t['ticket_id'],
            'title' => $t['subject'],
            'detail' => $t['message'],
            'status' => $t['ticket_status'],
            'created_at' => $t['created_at'],
        ];
    }
    usort($issues, fn($a, $b) => strcmp((string)$b['created_at'], (string)$a['created_at']));

    echo json_encode(['ok' => true, 'stall_id' => (int)$stallId, 'issues' => $issues]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
