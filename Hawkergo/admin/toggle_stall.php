<?php
session_start();
include("../connection/connect.php");
error_reporting(0);

if (empty($_SESSION['adm_id'])) {
    header('Location: index.php');
    exit;
}

$rs_id = isset($_GET['rs_id']) ? intval($_GET['rs_id']) : 0;
if ($rs_id > 0) {
    $stmt = $db->prepare("UPDATE hawkerstalls SET accepting_orders = IF(accepting_orders = 1, 0, 1) WHERE rs_id = ?");
    $stmt->bind_param('i', $rs_id);
    $stmt->execute();
    $stmt->close();
}

header('Location: stall_control.php');
exit;
