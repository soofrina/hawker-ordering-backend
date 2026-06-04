<?php
// ════════════════════════════════════════════════════════════════
// ADD MENU ITEM (POST)
//   { "stall_id": 1, "item_name": "...", "base_price": 4.50, "description": "...", "image_url": "..." }
// NOTE: In production, stall_id will come from the authenticated user, not the request.
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
$name = trim($input['item_name'] ?? '');
$price = $input['base_price'] ?? null;
$desc = trim($input['description'] ?? '');
$img = trim($input['image_url'] ?? '');

if (!$stallId || !$name || $price === null) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Need stall_id, item_name, base_price']);
    exit;
}

try {
    $pdo = db();
    $stmt = $pdo->prepare(
        "INSERT INTO menu_item (stall_id, item_name, description, base_price, image_url, availability)
         VALUES (?, ?, ?, ?, ?, 1)"
    );
    $stmt->execute([$stallId, $name, $desc, $price, $img]);
    $itemId = (int)$pdo->lastInsertId();
    echo json_encode(['ok' => true, 'item_id' => $itemId]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
