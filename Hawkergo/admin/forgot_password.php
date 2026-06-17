<?php
session_start();
include('../connection/connect.php');
error_reporting(0);

if (!empty($_SESSION['adm_id'])) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$success = false;
$reset_link = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reset'])) {
    $result = hawkergo_admin_request_password_reset(
        $db,
        isset($_POST['email']) ? $_POST['email'] : ''
    );
    $message = $result['message'];
    $success = !empty($result['success']);
    if ($success && !empty($result['token']) && !empty($result['email'])) {
        $reset_link = 'reset_password.php?token=' . urlencode($result['token'])
            . '&email=' . urlencode($result['email']);
    }
}

$feature_labels = array(
    'Manage Customers',
    'Manage Hawker Stalls',
    'Payment Issues',
    'Sales Reports',
    'All Orders'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password | HawkerGo Admin</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/customer-bites.css" rel="stylesheet">
    <link href="css/admin-portal.css" rel="stylesheet">
</head>
<body class="hg-admin-portal hg-admin-login-body">
<div class="hg-admin-login-shell">
    <section class="hg-admin-login-showcase">
        <div class="hg-admin-login-showcase-inner">
            <?php $hg_admin_brand_size = 'lg'; include __DIR__ . '/includes/admin_brand.php'; ?>
            <div class="js-admin-feature-rotator hg-admin-feature-rotator" data-labels="<?php echo htmlspecialchars(json_encode($feature_labels), ENT_QUOTES, 'UTF-8'); ?>">
                <span class="hg-admin-feature-rotator-label">System control</span>
                <div class="js-admin-feature-rotator-body hg-admin-feature-rotator-body"></div>
            </div>
        </div>
    </section>

    <section class="hg-admin-login-panel">
        <div class="hg-admin-login-card">
            <h1>Forgot password</h1>
            <p class="lead">Enter your admin email to generate a password reset link.</p>

            <?php if ($message !== '') { ?>
                <div class="hg-admin-alert <?php echo $success ? 'hg-admin-alert--success' : 'hg-admin-alert--error'; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php } ?>

            <?php if ($reset_link !== '') { ?>
                <div class="hg-admin-reset-box">
                    <p class="small text-muted mb-1">Use this link to set a new password (valid 1 hour):</p>
                    <a class="hg-admin-reset-link" href="<?php echo htmlspecialchars($reset_link); ?>"><?php echo htmlspecialchars($reset_link); ?></a>
                </div>
            <?php } ?>

            <form class="hg-admin-login-form" method="post" action="">
                <div class="hg-admin-form-group">
                    <label for="email">Admin email</label>
                    <input type="email" id="email" name="email" required placeholder="admin@mail.com">
                </div>
                <button type="submit" name="request_reset" value="1" class="hg-btn hg-btn--gold btn-block">Send reset link</button>
            </form>

            <div class="hg-admin-footer-links">
                <a href="index.php"><i class="fa fa-arrow-left"></i> Back to login</a>
                <span class="text-muted"> · </span>
                <a href="register.php">Register new admin</a>
            </div>
        </div>
    </section>
</div>
<script src="../js/jquery.min.js"></script>
<script src="js/admin-portal.js"></script>
</body>
</html>
