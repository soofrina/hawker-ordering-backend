<?php
session_start();
include('../connection/connect.php');
error_reporting(0);

if (isset($_POST['submit'])) {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (!empty($_POST['submit'])) {
        $loginquery = "SELECT * FROM admin WHERE username='$username' && password='" . md5($password) . "'";
        $result = mysqli_query($db, $loginquery);
        $row = mysqli_fetch_array($result);

        if (is_array($row)) {
            $_SESSION['adm_id'] = $row['adm_id'];
            header('Location: dashboard.php');
            exit;
        }
        $message = 'Invalid username or password.';
    }
}

if (!empty($_SESSION['adm_id'])) {
    header('Location: dashboard.php');
    exit;
}

$message = isset($message) ? $message : '';
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
    <title>Admin Login | HawkerGo</title>
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
            <h1>Sign in</h1>
            <p class="lead">HawkerGo admin — manage stalls, orders, users, and payments.</p>

            <?php if ($message !== '') { ?>
                <div class="hg-admin-alert hg-admin-alert--error"><?php echo htmlspecialchars($message); ?></div>
            <?php } ?>

            <form class="hg-admin-login-form" action="index.php" method="post">
                <div class="hg-admin-form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Admin username" required>
                </div>
                <div class="hg-admin-form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" name="submit" value="1" class="hg-btn hg-btn--gold btn-block">Login to Admin</button>
            </form>

            <div class="hg-admin-footer-links">
                <a href="register.php">Register new admin</a>
                <span class="text-muted"> · </span>
                <a href="forgot_password.php">Forgot password</a>
                <br>
                <a href="../hawker/index.php">Hawker portal</a>
                <span class="text-muted"> · </span>
                <a href="../customer/index.php">Customer site</a>
            </div>
        </div>
    </section>
</div>
<script src="../js/jquery.min.js"></script>
<script src="js/admin-portal.js"></script>
</body>
</html>
