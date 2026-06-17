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
$stalls = hawkergo_get_stalls_for_hawker_registration($db);
$available_count = 0;
foreach ($stalls as $stall) {
    if (empty($stall['taken'])) {
        $available_count++;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $cpassword = isset($_POST['cpassword']) ? $_POST['cpassword'] : '';
    if ($password !== $cpassword) {
        $message = 'Passwords do not match.';
    } else {
    $result = hawkergo_hawker_register(
        $db,
        isset($_POST['rs_id']) ? $_POST['rs_id'] : 0,
        isset($_POST['username']) ? $_POST['username'] : '',
        isset($_POST['email']) ? $_POST['email'] : '',
        $password
    );
    $message = $result['message'];
    $success = !empty($result['success']);
    if ($success) {
        $stalls = hawkergo_get_stalls_for_hawker_registration($db);
        $available_count = 0;
        foreach ($stalls as $stall) {
            if (empty($stall['taken'])) {
                $available_count++;
            }
        }
    }
    }
}

$stall_titles = array();
foreach ($stalls as $stall) {
    $stall_titles[] = $stall['title'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hawker Register | HawkerGo</title>
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
                <span class="hg-hawker-stall-rotator-label">Stalls on HawkerGo</span>
                <div class="js-hawker-stall-rotator-body hg-hawker-stall-rotator-body"></div>
            </div>
            <?php } ?>
        </div>
    </section>

    <section class="hg-hawker-login-panel">
        <div class="hg-hawker-login-card hg-hawker-login-card--wide">
            <h1>Create hawker account</h1>
            <p class="lead">Link your portal login to a hawker stall from the database.</p>

            <?php if ($message !== '') { ?>
                <div class="hg-hawker-alert <?php echo $success ? 'hg-hawker-alert--success' : 'hg-hawker-alert--error'; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php } ?>

            <?php if (empty($stalls)) { ?>
                <p class="text-muted">No hawker stalls found. Ask admin to add a stall first.</p>
            <?php } elseif ($available_count === 0) { ?>
                <p class="text-muted">All stalls already have portal accounts. Use <a href="forgot_password.php">forgot password</a> or contact admin.</p>
            <?php } else { ?>
            <form method="post" action="">
                <div class="hg-hawker-form-group">
                    <label for="rs_id">Select hawker stall</label>
                    <?php
                    $stall_picker_stalls = $stalls;
                    $stall_picker_select_available_only = true;
                    include __DIR__ . '/includes/stall_picker.php';
                    ?>
                </div>

                <div class="hg-hawker-form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required placeholder="Choose a username">
                </div>
                <div class="hg-hawker-form-group">
                    <label for="email">Email <span class="text-muted">(for password reset)</span></label>
                    <input type="email" id="email" name="email" placeholder="you@example.com">
                </div>
                <div class="hg-hawker-form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="At least 6 characters" minlength="6">
                </div>
                <div class="hg-hawker-form-group">
                    <label for="cpassword">Confirm password</label>
                    <input type="password" id="cpassword" name="cpassword" required placeholder="Confirm password" minlength="6">
                </div>
                <button type="submit" name="register" value="1" class="hg-btn hg-btn--gold btn-block">Create hawker account</button>
            </form>
            <?php } ?>

            <div class="hg-hawker-footer-links">
                <a href="index.php"><i class="fa fa-arrow-left"></i> Back to login</a>
                <span class="text-muted"> · </span>
                <a href="forgot_password.php">Forgot password</a>
            </div>
        </div>
    </section>
</div>
<script src="../js/jquery.min.js"></script>
<script src="js/hawker-portal.js"></script>
</body>
</html>
