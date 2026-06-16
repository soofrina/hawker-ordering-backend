<?php
// ════════════════════════════════════════════════════════════════
// GET STALL (GET) — a stall's profile, for the Settings screen.
//   api/get_stall.php?stall_id=1
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
    $stmt = $pdo->prepare(
        "SELECT stall_id, stall_name, location, phone, email, image_url, open_status, is_active
         FROM stall WHERE stall_id = ?"
    );
    $stmt->execute([$stallId]);
    $stall = $stmt->fetch();
    if (!$stall) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Stall not found']);
        exit;
    }
    echo json_encode(['ok' => true, 'stall' => $stall]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
