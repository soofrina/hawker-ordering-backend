<?php
// ════════════════════════════════════════════════════════════════
// UPDATE MENU ITEM (POST)
//   { "item_id": 1, "item_name": "...", "base_price": 5.00, ... }
// Partial update — only sends fields that changed.
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

$itemId = $input['item_id'] ?? null;
if (!$itemId) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Need item_id']);
    exit;
}

$updates = [];
$params = [$itemId];
if (isset($input['item_name'])) {
    $updates[] = 'item_name = ?';
    $params[] = trim($input['item_name']);
}
if (isset($input['description'])) {
    $updates[] = 'description = ?';
    $params[] = trim($input['description']);
}
if (isset($input['base_price'])) {
    $updates[] = 'base_price = ?';
    $params[] = $input['base_price'];
}
if (isset($input['image_url'])) {
    $updates[] = 'image_url = ?';
    $params[] = trim($input['image_url']);
}

if (empty($updates)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'No fields to update']);
    exit;
}

try {
    $pdo = db();
    $sql = "UPDATE menu_item SET " . implode(', ', $updates) . " WHERE item_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
