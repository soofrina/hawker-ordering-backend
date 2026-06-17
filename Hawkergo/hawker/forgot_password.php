<?php
session_start();
include('../connection/connect.php');
error_reporting(0);

if (hawkergo_hawker_is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$success = false;
$reset_link = '';
$registered_stalls = hawkergo_get_registered_hawker_stalls($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reset'])) {
    $result = hawkergo_hawker_request_password_reset(
        $db,
        isset($_POST['rs_id']) ? $_POST['rs_id'] : 0
    );
    $message = $result['message'];
    $success = !empty($result['success']);
    if ($success && !empty($result['token'])) {
        $reset_link = 'reset_password.php?token=' . urlencode($result['token']);
    }
}

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
    <title>Forgot Password | HawkerGo</title>
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
            <h1>Forgot password</h1>
            <p class="lead">Select your hawker stall to reset your portal password.</p>

            <?php if ($message !== '') { ?>
                <div class="hg-hawker-alert <?php echo $success ? 'hg-hawker-alert--success' : 'hg-hawker-alert--error'; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php } ?>

            <?php if ($reset_link !== '') { ?>
                <div class="hg-hawker-reset-box">
                    <p class="small text-muted mb-1">Use this link to set a new password (valid 1 hour):</p>
                    <a class="hg-hawker-reset-link" href="<?php echo htmlspecialchars($reset_link); ?>"><?php echo htmlspecialchars($reset_link); ?></a>
                </div>
            <?php } ?>

            <?php if (empty($registered_stalls)) { ?>
                <p class="text-muted">No registered hawker stalls found. <a href="register.php">Register first</a>.</p>
            <?php } else { ?>
            <form method="post" action="">
                <div class="hg-hawker-form-group">
                    <label for="rs_id">Your hawker stall</label>
                    <?php
                    $stall_picker_stalls = $registered_stalls;
                    include __DIR__ . '/includes/stall_picker.php';
                    ?>
                </div>
                <button type="submit" name="request_reset" value="1" class="hg-btn hg-btn--gold btn-block">Send reset link</button>
            </form>
            <?php } ?>

            <div class="hg-hawker-footer-links">
                <a href="index.php"><i class="fa fa-arrow-left"></i> Back to login</a>
                <span class="text-muted"> · </span>
                <a href="register.php">Register new hawker</a>
            </div>
        </div>
    </section>
</div>
<script src="../js/jquery.min.js"></script>
<script src="js/hawker-portal.js"></script>
</body>
</html>
