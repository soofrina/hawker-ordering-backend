<?php
session_start();
include('../connection/connect.php');
error_reporting(0);

$message = '';

if (isset($_POST['login'])) {
    $result = hawkergo_hawker_login(
        $db,
        isset($_POST['rs_id']) ? $_POST['rs_id'] : 0,
        isset($_POST['password']) ? $_POST['password'] : ''
    );
    if ($result['success']) {
        header('Location: dashboard.php');
        exit;
    }
    $message = $result['message'];
}

if (hawkergo_hawker_is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$registered_stalls = hawkergo_get_registered_hawker_stalls($db);
$stall_titles = array();
foreach ($registered_stalls as $stall) {
    $stall_titles[] = $stall['title'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hawker Login | HawkerGo</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/customer-bites.css" rel="stylesheet">
    <link href="../css/hawker-portal.css" rel="stylesheet">
</head>
<body class="hg-hawker-portal">
<div class="hg-hawker-login-shell">
    <section class="hg-hawker-login-showcase">
        <div class="hg-hawker-login-showcase-inner">
            <?php include __DIR__ . '/includes/hawker_brand.php'; ?>

            <?php if (!empty($stall_titles)) { ?>
            <div class="js-hawker-stall-rotator hg-hawker-stall-rotator" data-stalls="<?php echo htmlspecialchars(json_encode($stall_titles), ENT_QUOTES, 'UTF-8'); ?>">
                <span class="hg-hawker-stall-rotator-label">Hawker stalls on HawkerGo</span>
                <div class="js-hawker-stall-rotator-body hg-hawker-stall-rotator-body"></div>
            </div>
            <?php } ?>
        </div>
    </section>

    <section class="hg-hawker-login-panel">
        <div class="hg-hawker-login-card hg-hawker-login-card--wide">
            <h1>Sign in</h1>
            <p class="lead">Select your hawker stall and enter your password.</p>

            <?php if ($message !== '') { ?>
                <div class="hg-hawker-alert hg-hawker-alert--error"><?php echo htmlspecialchars($message); ?></div>
            <?php } ?>

            <?php if (empty($registered_stalls)) { ?>
                <p class="text-muted">No hawker stalls with portal accounts yet. <a href="register.php">Register first</a>.</p>
            <?php } else { ?>
            <form method="post" action="">
                <div class="hg-hawker-form-group">
                    <label for="rs_id">Your hawker stall</label>
                    <?php
                    $stall_picker_stalls = $registered_stalls;
                    include __DIR__ . '/includes/stall_picker.php';
                    ?>
                </div>
                <div class="hg-hawker-form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter password">
                </div>
                <button type="submit" name="login" value="1" class="hg-btn hg-btn--gold btn-block">Login to Portal</button>
            </form>
            <?php } ?>

            <div class="hg-hawker-footer-links">
                <a href="register.php">Register new hawker</a>
                <span class="text-muted"> · </span>
                <a href="forgot_password.php">Forgot password</a>
                <br>
                <a href="../customer/index.php"><i class="fa fa-arrow-left"></i> Back to customer site</a>
                <span class="text-muted"> · </span>
                <a href="../admin/index.php">Admin login</a>
            </div>
        </div>
    </section>
</div>
<script src="../js/jquery.min.js"></script>
<script src="js/hawker-portal.js"></script>
</body>
</html>
