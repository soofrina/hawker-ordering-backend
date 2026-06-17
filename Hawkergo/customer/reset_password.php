<?php
session_start();
include(__DIR__ . '/../connection/connect.php');
error_reporting(0);

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $token = isset($_POST['token']) ? trim($_POST['token']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $cpassword = isset($_POST['cpassword']) ? $_POST['cpassword'] : '';

    if ($password !== $cpassword) {
        $message = 'Passwords do not match.';
    } else {
        $result = hawkergo_customer_reset_password($db, $token, $password);
        $message = $result['message'];
        $success = !empty($result['success']);
    }
}

if ($token === '' && $message === '') {
    $message = 'Missing reset token. Request a new link from forgot password.';
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
    <title>Reset Password | HawkerGo</title>
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
            <h1>Set new password</h1>
            <p class="lead">Choose a new password for your HawkerGo customer account.</p>

            <?php if ($message !== '') { ?>
                <div class="hg-hawker-alert <?php echo $success ? 'hg-hawker-alert--success' : 'hg-hawker-alert--error'; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php } ?>

            <?php if ($success) { ?>
                <a href="login.php" class="hg-btn hg-btn--gold btn-block">Go to login</a>
            <?php } elseif ($token !== '') { ?>
            <form method="post" action="" id="resetPasswordForm">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="hg-hawker-form-group">
                    <label for="password">New password</label>
                    <input type="password" id="password" name="password" required minlength="6" placeholder="At least 6 characters">
                </div>
                <div class="hg-hawker-form-group">
                    <label for="cpassword">Confirm password</label>
                    <input type="password" id="cpassword" name="cpassword" required minlength="6" placeholder="Confirm password">
                </div>
                <button type="submit" name="reset_password" value="1" class="hg-btn hg-btn--gold btn-block">Update password</button>
            </form>
            <?php } ?>

            <div class="hg-hawker-footer-links">
                <a href="forgot_password.php">Request new link</a>
                <span class="text-muted"> · </span>
                <a href="login.php"><i class="fa fa-arrow-left"></i> Back to login</a>
            </div>
        </div>
    </section>
</div>
<script src="../js/jquery.min.js"></script>
<script src="../js/customer-auth-portal.js"></script>
<script>
(function () {
    var form = document.getElementById('resetPasswordForm');
    if (!form) return;
    form.addEventListener('submit', function (e) {
        var pw = document.getElementById('password').value;
        var cpw = document.getElementById('cpassword').value;
        if (pw !== cpw) {
            e.preventDefault();
            alert('Passwords do not match');
        }
    });
})();
</script>
</body>
</html>
