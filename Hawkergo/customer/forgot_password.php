<?php
session_start();
include(__DIR__ . '/../connection/connect.php');
error_reporting(0);

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';
$success = false;
$reset_link = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reset'])) {
    $result = hawkergo_customer_request_password_reset(
        $db,
        isset($_POST['identifier']) ? $_POST['identifier'] : ''
    );
    $message = $result['message'];
    $success = !empty($result['success']);
    if ($success && !empty($result['token'])) {
        $reset_link = 'reset_password.php?token=' . urlencode($result['token']);
    }
}

$feature_labels = array(
    'Track Your Orders',
    'Save Your Cart',
    'Order from Stalls',
    'Review Dishes',
    'Fast Checkout'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password | HawkerGo</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/theme.css" rel="stylesheet">
    <?php include __DIR__ . '/../includes/customer_styles.php'; ?>
    <link href="../css/hawker-portal.css" rel="stylesheet">
    <link href="../css/customer-auth-portal.css" rel="stylesheet">
</head>
<body class="hg-customer-auth-portal hg-hawker-portal">
<?php $nav_current = 'login'; include __DIR__ . '/../includes/customer_nav.php'; ?>
<div class="hg-hawker-login-shell">
    <section class="hg-hawker-login-showcase">
        <div class="hg-hawker-login-showcase-inner">
            <?php $hg_customer_brand_size = 'lg'; include __DIR__ . '/../includes/customer_auth_brand.php'; ?>

            <div class="js-customer-feature-rotator hg-customer-feature-rotator" data-labels="<?php echo htmlspecialchars(json_encode($feature_labels), ENT_QUOTES, 'UTF-8'); ?>">
                <span class="hg-customer-feature-rotator-label">Why HawkerGo</span>
                <div class="js-customer-feature-rotator-body hg-customer-feature-rotator-body"></div>
            </div>
        </div>
    </section>

    <section class="hg-hawker-login-panel">
        <div class="hg-hawker-login-card">
            <h1>Forgot password</h1>
            <p class="lead">Enter your username or email to reset your HawkerGo account password.</p>

            <?php if ($message !== '') { ?>
                <div class="hg-hawker-alert <?php echo $success ? 'hg-hawker-alert--success' : 'hg-hawker-alert--error'; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php } ?>

            <?php if ($reset_link !== '') { ?>
                <div class="hg-hawker-reset-box">
                    <p class="small text-muted mb-1">Use this link to set a new password (valid 1 hour):</p>
                    <a class="hg-hawker-reset-link" href="<?php echo htmlspecialchars($reset_link); ?>"><?php echo htmlspecialchars($reset_link); ?></a>
                </div>
            <?php } ?>

            <form method="post" action="">
                <div class="hg-hawker-form-group">
                    <label for="identifier">Username or email</label>
                    <input type="text" id="identifier" name="identifier" required placeholder="Your username or email" value="<?php echo isset($_POST['identifier']) ? htmlspecialchars($_POST['identifier']) : ''; ?>">
                </div>
                <button type="submit" name="request_reset" value="1" class="hg-btn hg-btn--gold btn-block">Send reset link</button>
            </form>

            <div class="hg-hawker-footer-links">
                <a href="login.php"><i class="fa fa-arrow-left"></i> Back to login</a>
                <span class="text-muted"> · </span>
                <a href="create_account.php">Create account</a>
                <br>
                <a href="index.php">Customer home</a>
                <span class="text-muted"> · </span>
                <a href="../hawker/index.php">Hawker portal</a>
            </div>
        </div>
    </section>
</div>
<script src="../js/jquery.min.js"></script>
<script src="../js/customer-auth-portal.js"></script>
</body>
</html>
