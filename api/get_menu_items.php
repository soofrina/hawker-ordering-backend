<?php
// ════════════════════════════════════════════════════════════════
// GET MENU ITEMS (GET) — list all items for a stall.
//   api/get_menu_items.php?stall_id=1
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
        "SELECT item_id, item_name, description, base_price, image_url, availability
         FROM menu_item WHERE stall_id = ? ORDER BY item_name"
    );
    $stmt->execute([$stallId]);
    $items = $stmt->fetchAll();
    echo json_encode(['ok' => true, 'stall_id' => (int)$stallId, 'items' => $items]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
