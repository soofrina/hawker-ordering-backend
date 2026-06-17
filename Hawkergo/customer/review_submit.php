<?php
session_start();
include(__DIR__ . '/../connection/connect.php');
error_reporting(0);

if (empty($_SESSION['user_id'])) {
    header('Location: login.php?return=your_orders.php');
    exit;
}

$user_id = intval($_SESSION['user_id']);
$redirect = 'your_orders.php';
$result_message = '';
$result_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $review_type = isset($_POST['review_type']) ? $_POST['review_type'] : '';
    $o_id = isset($_POST['o_id']) ? intval($_POST['o_id']) : 0;
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = isset($_POST['comment']) ? $_POST['comment'] : '';

    if ($review_type === 'dish') {
        $result = hawkergo_submit_dish_review($db, $user_id, $o_id, $rating, $comment);
    } elseif ($review_type === 'stall') {
        $result = hawkergo_submit_stall_review($db, $user_id, $o_id, $rating, $comment);
    } else {
        $result = array('success' => false, 'message' => 'Invalid review type.');
    }

    $result_type = $result['success'] ? 'success' : 'error';
    $_SESSION['review_notice'] = array(
        'type' => $result_type,
        'message' => $result['message']
    );
    header('Location: ' . $redirect);
    exit;
}

header('Location: ' . $redirect);
exit;
