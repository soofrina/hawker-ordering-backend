<?php
session_start();
include(__DIR__ . '/../connection/connect.php');
error_reporting(0);

$orders = array();
$lookup_error = '';

if (!empty($_SESSION['order_verification_code']) && isset($_SESSION['order_queue_number']) && empty($_SESSION['order_confirmation'])) {
    $_SESSION['order_confirmation'] = array(
        'queue_number' => intval($_SESSION['order_queue_number']),
        'verification_code' => $_SESSION['order_verification_code'],
        'queues_by_stall' => array(),
        'order_type' => 'takeaway',
        'is_guest' => empty($_SESSION['user_id']),
        'guest_phone' => isset($_SESSION['guest_phone']) ? $_SESSION['guest_phone'] : '',
        'email' => ''
    );
    header('Location: order_thankyou.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lookup'])) {
    $phone = trim($_POST['guest_phone']);
    $code = trim($_POST['verification_code']);

    if ($phone === '' || $code === '') {
        $lookup_error = 'Please enter your contact number and verification code.';
    } else {
        $orders = hawkergo_find_orders_by_phone_and_code($db, $phone, $code);
        if (empty($orders)) {
            $lookup_error = 'No order found for those details. Use the same phone number from checkout or your account, and check the code is correct.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Lookup - HawkerGo</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/theme.css" rel="stylesheet">
    <?php include __DIR__ . '/../includes/customer_styles.php'; ?>
    <style>
        body { background: #f2f6fc; }
        .orders-card { background: #fff; border-radius: 18px; padding: 1.5rem; box-shadow: 0 18px 45px rgba(8,34,79,.08); }
        .queue-banner { background: linear-gradient(135deg,#5268f5,#3856d4); color: #fff; border-radius: 14px; padding: 1.25rem; margin-bottom: 1.25rem; }
        .queue-banner h3 { margin: 0; font-weight: 700; }
    </style>
</head>
<body class="home">
<?php $nav_current = 'guest'; include __DIR__ . '/../includes/customer_nav.php'; ?>

<div class="page-wrapper">
<div class="container pb-5">
    <div class="orders-card">
        <h2 class="mb-3">Order Lookup</h2>
        <p class="text-muted">Enter your contact number and verification code to view your order. Works for guest orders and registered accounts.</p>

        <form method="post" class="mb-4">
            <div class="form-row">
                <div class="form-group col-md-5">
                    <label>Contact number</label>
                    <input type="tel" name="guest_phone" class="form-control" required placeholder="e.g. +65 8745260"
                           value="<?php echo isset($_POST['guest_phone']) ? htmlspecialchars($_POST['guest_phone']) : (isset($_SESSION['guest_phone']) ? htmlspecialchars($_SESSION['guest_phone']) : ''); ?>">
                </div>
                <div class="form-group col-md-5">
                    <label>Verification code</label>
                    <input type="text" name="verification_code" class="form-control" required placeholder="e.g. 2A1FC2" style="text-transform:uppercase"
                           value="<?php echo isset($_POST['verification_code']) ? htmlspecialchars($_POST['verification_code']) : ''; ?>">
                </div>
                <div class="form-group col-md-2 d-flex align-items-end">
                    <button type="submit" name="lookup" class="btn btn-purple btn-block">Find Order</button>
                </div>
            </div>
        </form>

        <?php if ($lookup_error) { ?><div class="alert alert-warning"><?php echo htmlspecialchars($lookup_error); ?></div><?php } ?>

        <?php if (!empty($orders)) { ?>
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr><th>Item</th><th>Hawker Stall</th><th>Qty</th><th>Price</th><th>Queue</th><th>Code</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $row) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo !empty($row['stall_name']) ? htmlspecialchars($row['stall_name']) : '-'; ?></td>
                        <td><?php echo intval($row['quantity']); ?></td>
                        <td>$<?php echo htmlspecialchars($row['price']); ?></td>
                        <td><?php echo ($row['queue_number'] !== null && $row['queue_number'] !== '') ? '#' . intval($row['queue_number']) : '-'; ?></td>
                        <td><?php echo htmlspecialchars($row['verification_code']); ?></td>
                        <td><?php echo htmlspecialchars($row['status'] ?: 'Preparing'); ?></td>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?php if (!empty($orders[0]['special_request'])) { ?>
                <p><strong>Special request:</strong> <?php echo htmlspecialchars($orders[0]['special_request']); ?></p>
            <?php } ?>
        <?php } ?>

        <p class="text-muted small mt-3 mb-0">Logged-in users can also view all orders on <a href="your_orders.php">My Orders</a>. Guest users can use this page to look up orders anytime.</p>
    </div>
</div>
</div>
<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/headroom.js"></script>
<script src="../js/customer-nav.js"></script>
</body>
</html>
