<?php
// Saves cart quantity changes from the cart page without a full form submit.
session_start();
include(__DIR__ . '/../connection/connect.php');
error_reporting(0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['qty']) && is_array($_POST['qty'])) {
    hawkergo_apply_cart_quantities($_POST['qty']);
}

$total = 0;
if (!empty($_SESSION['cart_item'])) {
    foreach ($_SESSION['cart_item'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
}

header('Content-Type: application/json');
echo json_encode(array('success' => true, 'total' => round($total, 2)));
