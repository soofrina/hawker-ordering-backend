<?php
// ════════════════════════════════════════════════════════════════
// UPLOAD IMAGE (POST, multipart/form-data) — form field: "photo"
// Saves the photo into /uploads and returns its URL:
//   { "ok": true, "url": "uploads/img_20260615_143000_a1b2c3d4.jpg" }
// The URL is relative, so it works the same on localhost and on Render
// (where /uploads should be backed by a persistent disk so files survive
//  redeploys).
// ════════════════════════════════════════════════════════════════
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

if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'No file received']);
    exit;
}
$f = $_FILES['photo'];

// Size guard (5 MB).
if ($f['size'] > 5 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Image too large (max 5 MB)']);
    exit;
}

// Confirm it's genuinely an image, and pick a safe extension from the real type.
$info = @getimagesize($f['tmp_name']);
$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
if (!$info || !isset($allowed[$info['mime']])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'That file is not a valid image']);
    exit;
}
$ext = $allowed[$info['mime']];

$dir = __DIR__ . '/../uploads';
if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Upload folder not writable']);
    exit;
}

$name = 'img_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not save the file']);
    exit;
}

echo json_encode(['ok' => true, 'url' => 'uploads/' . $name]);
