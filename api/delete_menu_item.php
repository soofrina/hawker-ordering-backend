<?php
// ════════════════════════════════════════════════════════════════
// DELETE MENU ITEM (POST)
//   { "item_id": 1 }
// Hard delete. (Could be soft delete if needed later.)
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
$itemId = $input['item_id'] ?? null;
if (!$itemId) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Need item_id']);
    exit;
}

try {
    $pdo = db();
    $stmt = $pdo->prepare("DELETE FROM menu_item WHERE item_id = ?");
    $stmt->execute([$itemId]);
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
