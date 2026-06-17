<?php
session_start();
include('../connection/connect.php');
error_reporting(0);
hawkergo_require_hawker_login();

$rs_id = hawkergo_hawker_rs_id();
$stmt = $db->prepare('UPDATE hawkerstalls SET accepting_orders = IF(accepting_orders = 1, 0, 1) WHERE rs_id = ?');
$stmt->bind_param('i', $rs_id);
$stmt->execute();
$stmt->close();

header('Location: stall_settings.php');
exit;
