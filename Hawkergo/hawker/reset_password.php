<?php
session_start();
include('../connection/connect.php');
error_reporting(0);

if (hawkergo_hawker_is_logged_in()) {
    header('Location: dashboard.php');
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
        $result = hawkergo_hawker_reset_password($db, $token, $password);
        $message = $result['message'];
        $success = !empty($result['success']);
    }
}

if ($token === '') {
    $message = 'Missing reset token. Request a new link from forgot password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password | HawkerGo</title>
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
        </div>
    </section>

    <section class="hg-hawker-login-panel">
        <div class="hg-hawker-login-card">
            <h1>Set new password</h1>
            <p class="lead">Choose a new password for your hawker portal account.</p>

            <?php if ($message !== '') { ?>
                <div class="hg-hawker-alert <?php echo $success ? 'hg-hawker-alert--success' : 'hg-hawker-alert--error'; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php } ?>

            <?php if ($success) { ?>
                <a href="index.php" class="hg-btn hg-btn--gold btn-block">Go to login</a>
            <?php } elseif ($token !== '') { ?>
            <form method="post" action="">
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
                <a href="index.php">Back to login</a>
            </div>
        </div>
    </section>
</div>
<script src="../js/jquery.min.js"></script>
<script src="js/hawker-portal.js"></script>
</body>
</html>
