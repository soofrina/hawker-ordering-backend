<?php
session_start();
include(__DIR__ . '/../connection/connect.php');
error_reporting(0);

function hawkergo_clear_order_confirmation()
{
    unset(
        $_SESSION['order_confirmation'],
        $_SESSION['order_queue_number'],
        $_SESSION['order_verification_code']
    );
}

function hawkergo_thankyou_redirect($is_guest)
{
    hawkergo_clear_order_confirmation();
    if ($is_guest) {
        header('Location: guest_orders.php');
    } elseif (!empty($_SESSION['user_id'])) {
        header('Location: your_orders.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$confirmation = !empty($_SESSION['order_confirmation']) && is_array($_SESSION['order_confirmation'])
    ? $_SESSION['order_confirmation']
    : null;

if (!$confirmation && !empty($_SESSION['order_verification_code']) && isset($_SESSION['order_queue_number'])) {
    $confirmation = array(
        'queue_number' => intval($_SESSION['order_queue_number']),
        'verification_code' => $_SESSION['order_verification_code'],
        'queues_by_stall' => array(),
        'order_type' => 'takeaway',
        'is_guest' => empty($_SESSION['user_id']),
        'guest_phone' => isset($_SESSION['guest_phone']) ? $_SESSION['guest_phone'] : '',
        'email' => ''
    );
}

if (!$confirmation) {
    header('Location: index.php');
    exit;
}

$queue_number = intval($confirmation['queue_number']);
$verification_code = $confirmation['verification_code'];
$order_type = isset($confirmation['order_type']) ? $confirmation['order_type'] : 'takeaway';
$is_guest = !empty($confirmation['is_guest']);
$guest_phone = isset($confirmation['guest_phone']) ? trim($confirmation['guest_phone']) : '';
$user_email = isset($confirmation['email']) ? trim($confirmation['email']) : '';

if ($user_email === '' && !empty($_SESSION['user_id'])) {
    $uid = intval($_SESSION['user_id']);
    $stmt = $db->prepare('SELECT email, phone FROM users WHERE u_id = ? LIMIT 1');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $user_row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($user_row) {
        $user_email = trim($user_row['email']);
        if ($guest_phone === '') {
            $guest_phone = trim($user_row['phone']);
        }
    }
}

$stall_queues = array();
if (!empty($confirmation['queues_by_stall']) && is_array($confirmation['queues_by_stall'])) {
    foreach ($confirmation['queues_by_stall'] as $rs_id => $stall_queue) {
        $stall_queues[] = array(
            'name' => hawkergo_get_stall_title($db, $rs_id),
            'queue' => intval($stall_queue)
        );
    }
}

$order_type_label = ($order_type === 'dine-in') ? 'Dine-In' : 'Take-Away';
$pickup_message = ($order_type === 'dine-in')
    ? 'Please head to the hawker stall and wait for your queue number to be called.'
    : 'Please head to the collection point to pick up your order.';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['done'])) {
    $receipt_email = isset($_POST['receipt_email']) ? trim($_POST['receipt_email']) : '';
    if ($receipt_email !== '' && filter_var($receipt_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['receipt_email_notice'] = 'E-receipt details saved for ' . $receipt_email . '.';
    }
    hawkergo_thankyou_redirect($is_guest);
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$site_base = $scheme . '://' . $host . $base_path;

$qr_lines = array(
    'HawkerGo Order',
    'Queue: ' . $queue_number,
    'Code: ' . $verification_code,
    'Type: ' . $order_type_label
);
if ($guest_phone !== '') {
    $qr_lines[] = 'Phone: ' . $guest_phone;
}
$qr_lines[] = $site_base . '/guest_orders.php';
$qr_data = implode("\n", $qr_lines);
$qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . rawurlencode($qr_data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Thank You - HawkerGo</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/theme.css" rel="stylesheet">
    <?php include __DIR__ . '/../includes/customer_styles.php'; ?>
    <link href="../css/order-thankyou.css" rel="stylesheet">
</head>
<body class="hg-thankyou-page">

<div class="hg-thankyou-kiosk">
    <div class="hg-thankyou-stripes"></div>

    <div class="hg-thankyou-brand">
        <?php include __DIR__ . '/../includes/hawkergo_logo.php'; ?>
    </div>

    <div class="hg-thankyou-body">
        <h1 class="hg-thankyou-title">THANK YOU.!</h1>

        <p class="hg-thankyou-note">Please note: No physical receipt will be provided.</p>
        <p class="hg-thankyou-scan">Scan the QR code to get your e-receipt</p>

        <div class="hg-thankyou-qr-wrap">
            <img class="hg-thankyou-qr" src="<?php echo htmlspecialchars($qr_url); ?>" alt="Order QR code" width="220" height="220">
            <span class="hg-thankyou-qr-badge"><i class="fa fa-cutlery"></i></span>
        </div>

        <p class="hg-thankyou-queue-label">YOUR ORDER NUMBER</p>
        <p class="hg-thankyou-queue-number"><?php echo $queue_number; ?></p>

        <p class="hg-thankyou-type"><?php echo htmlspecialchars($order_type_label); ?> &middot; Verification <?php echo htmlspecialchars($verification_code); ?></p>

        <?php if (count($stall_queues) > 1) { ?>
            <ul class="hg-thankyou-stall-list">
                <?php foreach ($stall_queues as $stall_queue) { ?>
                    <li><strong><?php echo htmlspecialchars($stall_queue['name']); ?></strong> — Queue #<?php echo intval($stall_queue['queue']); ?></li>
                <?php } ?>
            </ul>
        <?php } elseif (count($stall_queues) === 1) { ?>
            <p class="hg-thankyou-queue-extra"><?php echo htmlspecialchars($stall_queues[0]['name']); ?></p>
        <?php } ?>

        <p class="hg-thankyou-pickup"><?php echo htmlspecialchars($pickup_message); ?></p>

        <?php if (!$is_guest && !empty($_SESSION['user_id'])) { ?>
            <p class="hg-thankyou-review-link">
                <i class="fa fa-star"></i>
                After you collect your order, leave a review in
                <a href="your_orders.php">My Orders</a>.
            </p>
        <?php } ?>

        <form method="post" action="order_thankyou.php">
            <input
                type="email"
                name="receipt_email"
                class="hg-thankyou-email"
                placeholder="ENTER EMAIL FOR E-RECEIPT"
                value="<?php echo htmlspecialchars($user_email); ?>"
            >
            <button type="submit" name="done" value="1" class="hg-thankyou-done">DONE</button>
        </form>
    </div>

    <div class="hg-thankyou-footer-img" aria-hidden="true">
        <i class="fa fa-shopping-bag"></i>
    </div>
</div>

</body>
</html>
