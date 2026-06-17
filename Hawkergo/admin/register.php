<?php
session_start();
include("../connection/connect.php");
error_reporting(0);
$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    if ($username === '' || $email === '' || $password === '' || $cpassword === '') {
        $message = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email address.';
    } elseif ($password !== $cpassword) {
        $message = 'Passwords do not match.';
    } else {
        $u = mysqli_real_escape_string($db, $username);
        $e = mysqli_real_escape_string($db, $email);
        $checkUser = mysqli_query($db, "SELECT 1 FROM admin WHERE username='$u' LIMIT 1");
        $checkEmail = mysqli_query($db, "SELECT 1 FROM admin WHERE email='$e' LIMIT 1");
        if ($checkUser && mysqli_num_rows($checkUser) > 0) {
            $message = 'Username already exists.';
        } elseif ($checkEmail && mysqli_num_rows($checkEmail) > 0) {
            $message = 'Email already exists.';
        } else {
            $passwordHash = md5($password);
            $nextIdRes = mysqli_query($db, 'SELECT COALESCE(MAX(adm_id), 0) + 1 AS next_id FROM admin');
            $nextId = 1;
            if ($nextIdRes && $row = mysqli_fetch_assoc($nextIdRes)) {
                $nextId = intval($row['next_id']);
            }
            $stmt = $db->prepare('INSERT INTO admin (adm_id, username, password, email, code) VALUES (?, ?, ?, ?, ?)');
            if ($stmt) {
                $code = '';
                $stmt->bind_param('issss', $nextId, $username, $passwordHash, $email, $code);
                if ($stmt->execute()) {
                    $message = 'Admin account created successfully. You may now login.';
                    $success = true;
                } else {
                    $message = 'Registration failed: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $message = 'Registration failed: ' . $db->error;
            }
        }
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
    <title>Admin Register | HawkerGo</title>
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
            <h1>Create admin</h1>
            <p class="lead">Register a new HawkerGo administrator account.</p>

            <?php if ($message !== '') { ?>
                <div class="hg-admin-alert <?php echo $success ? 'hg-admin-alert--success' : 'hg-admin-alert--error'; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php } ?>

            <form class="hg-admin-login-form" action="register.php" method="post">
                <div class="hg-admin-form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Admin username" required>
                </div>
                <div class="hg-admin-form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="admin@example.com" required>
                </div>
                <div class="hg-admin-form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
                <div class="hg-admin-form-group">
                    <label for="cpassword">Confirm password</label>
                    <input type="password" id="cpassword" name="cpassword" placeholder="Confirm password" required>
                </div>
                <button type="submit" name="submit" value="1" class="hg-btn hg-btn--gold btn-block">Create account</button>
            </form>

            <div class="hg-admin-footer-links">
                <a href="index.php">Back to login</a>
                <span class="text-muted"> · </span>
                <a href="forgot_password.php">Forgot password</a>
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
