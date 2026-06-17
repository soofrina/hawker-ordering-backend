<?php
session_start();
include("../connection/connect.php");
error_reporting(0);

if (empty($_SESSION['adm_id'])) {
    header('Location: index.php');
    exit;
}

$d_id = isset($_GET['d_id']) ? intval($_GET['d_id']) : 0;
if ($d_id > 0) {
    $stmt = $db->prepare("UPDATE dishes SET is_sold_out = IF(is_sold_out = 1, 0, 1) WHERE d_id = ?");
    $stmt->bind_param('i', $d_id);
    $stmt->execute();
    $stmt->close();
}

header('Location: all_menu.php');
exit;
