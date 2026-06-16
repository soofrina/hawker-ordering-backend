<?php
// ════════════════════════════════════════════════════════════════
// UPDATE STALL PROFILE (POST)
//   { "stall_id": 1, "stall_name": "...", "phone": "...", "email": "...", "image_url": "..." }
// Partial — only sends fields that changed.
// (NOTE: in production, stall_id comes from the logged-in hawker.)
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
if (!$stallId) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Need stall_id']);
    exit;
}

$updates = [];
$params = [];
foreach (['stall_name', 'phone', 'email', 'image_url'] as $f) {
    if (isset($input[$f])) {
        $updates[] = "$f = ?";
        $params[] = trim($input[$f]);
    }
}
if (empty($updates)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'No fields to update']);
    exit;
}
$params[] = $stallId;

try {
    $pdo = db();
    $sql = "UPDATE stall SET " . implode(', ', $updates) . " WHERE stall_id = ?";
    $pdo->prepare($sql)->execute($params);
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
