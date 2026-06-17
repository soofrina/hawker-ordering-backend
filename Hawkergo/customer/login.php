<?php
session_start();
include(__DIR__ . '/../connection/connect.php');
error_reporting(0);

$message = '';
$success = '';
$notice = '';

if (isset($_GET['return']) && $_GET['return'] === 'checkout') {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    if ($notice === '') {
        $notice = 'Please login to complete your order. Your cart is saved.';
    }
}

if (isset($_POST['submit'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($username === '' || $password === '') {
        $message = 'Please enter your username and password.';
    } else {
        $stmt = $db->prepare('SELECT u_id, password FROM users WHERE username = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->bind_result($uid, $hash);
            if ($stmt->fetch() && password_verify($password, $hash)) {
                $_SESSION['user_id'] = $uid;
                $stmt->close();
                $redirect = 'index.php';
                if (!empty($_SESSION['redirect_after_login'])) {
                    $redirect = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                }
                header('Location: ' . $redirect);
                exit;
            }
            $message = 'Invalid username or password.';
            $stmt->close();
        } else {
            $message = 'Login error. Please try again.';
        }
    }
}

if (!empty($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (!empty($_SESSION['checkout_login_notice'])) {
    $notice = $_SESSION['checkout_login_notice'];
    unset($_SESSION['checkout_login_notice']);
    if (empty($_SESSION['redirect_after_login'])) {
        $_SESSION['redirect_after_login'] = 'checkout.php';
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
    <title>Login | HawkerGo</title>
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
            <h1>Sign in</h1>
            <p class="lead">Welcome back — order from hawker stalls and track your queue number.</p>

            <?php if ($notice !== '') { ?>
                <div class="hg-hawker-alert hg-hawker-alert--warn"><i class="fa fa-shopping-cart"></i> <?php echo htmlspecialchars($notice); ?></div>
            <?php } ?>
            <?php if ($message !== '') { ?>
                <div class="hg-hawker-alert hg-hawker-alert--error"><?php echo htmlspecialchars($message); ?></div>
            <?php } ?>
            <?php if ($success !== '') { ?>
                <div class="hg-hawker-alert hg-hawker-alert--success"><?php echo htmlspecialchars($success); ?></div>
            <?php } ?>

            <form method="post" action="">
                <div class="hg-hawker-form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                <div class="hg-hawker-form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                    <div class="hg-customer-forgot-wrap">
                        <a href="forgot_password.php">Forgot password?</a>
                    </div>
                </div>
                <button type="submit" name="submit" value="1" class="hg-btn hg-btn--gold btn-block">Login</button>
            </form>

            <div class="hg-hawker-footer-links">
                <a href="create_account.php">Create account</a>
                <span class="text-muted"> · </span>
                <a href="index.php">Continue as guest</a>
                <span class="text-muted"> · </span>
                <a href="guest_orders.php">Track guest order</a>
                <br>
                <a href="index.php"><i class="fa fa-arrow-left"></i> Back to home</a>
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
