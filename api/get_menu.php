<?php
// Example READ endpoint: returns the menu as JSON.
// Column names match the team's final ERD (menu_item table).
require __DIR__ . '/../db.php';
header('Content-Type: application/json');

try {
    $stmt = db()->query(
        "SELECT item_id, stall_id, item_name, base_price, availability
         FROM menu_item
         ORDER BY stall_id, item_name"
    );
    echo json_encode(['ok' => true, 'items' => $stmt->fetchAll()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
