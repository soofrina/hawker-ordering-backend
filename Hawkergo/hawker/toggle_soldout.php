<?php
session_start();
include('../connection/connect.php');
error_reporting(0);
hawkergo_require_hawker_login();

$rs_id = hawkergo_hawker_rs_id();
$d_id = isset($_GET['d_id']) ? intval($_GET['d_id']) : 0;

if ($d_id > 0 && hawkergo_hawker_dish_belongs_to_stall($db, $d_id, $rs_id)) {
    $stmt = $db->prepare('UPDATE dishes SET is_sold_out = IF(is_sold_out = 1, 0, 1) WHERE d_id = ? AND rs_id = ?');
    $stmt->bind_param('ii', $d_id, $rs_id);
    $stmt->execute();
    $stmt->close();
}

header('Location: menu.php');
exit;
