<?php
session_start();
error_reporting(0);

$error = '';
if (!empty($_SESSION['register_error'])) {
    $error = $_SESSION['register_error'];
    unset($_SESSION['register_error']);
}

$old = array(
    'username' => '',
    'firstname' => '',
    'lastname' => '',
    'email' => '',
    'phone' => ''
);
if (!empty($_SESSION['register_old']) && is_array($_SESSION['register_old'])) {
    $old = array_merge($old, $_SESSION['register_old']);
    unset($_SESSION['register_old']);
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
    <title>Create Account | HawkerGo</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/theme.css" rel="stylesheet">
    <?php include __DIR__ . '/../includes/customer_styles.php'; ?>
    <link href="../css/hawker-portal.css" rel="stylesheet">
    <link href="../css/customer-auth-portal.css" rel="stylesheet">
</head>
<body class="hg-customer-auth-portal hg-hawker-portal">
<?php $nav_current = 'register'; include __DIR__ . '/../includes/customer_nav.php'; ?>
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
        <div class="hg-hawker-login-card hg-hawker-login-card--wide">
            <h1>Create account</h1>
            <p class="lead">Join HawkerGo to order from hawker stalls and track your orders.</p>

            <?php if ($error !== '') { ?>
                <div class="hg-hawker-alert hg-hawker-alert--error"><?php echo htmlspecialchars($error); ?></div>
            <?php } ?>

            <form id="createAccountForm" action="registration.php" method="post" novalidate>
                <div class="hg-customer-auth-form-grid">
                    <div class="hg-hawker-form-group hg-customer-auth-form-full">
                        <label for="username">Username</label>
                        <input id="username" name="username" type="text" required value="<?php echo htmlspecialchars($old['username']); ?>">
                    </div>
                    <div class="hg-hawker-form-group">
                        <label for="firstname">First name</label>
                        <input id="firstname" name="firstname" type="text" required value="<?php echo htmlspecialchars($old['firstname']); ?>">
                    </div>
                    <div class="hg-hawker-form-group">
                        <label for="lastname">Last name</label>
                        <input id="lastname" name="lastname" type="text" required value="<?php echo htmlspecialchars($old['lastname']); ?>">
                    </div>
                    <div class="hg-hawker-form-group">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" required value="<?php echo htmlspecialchars($old['email']); ?>">
                    </div>
                    <div class="hg-hawker-form-group">
                        <label for="phone">Phone</label>
                        <input id="phone" name="phone" type="tel" required value="<?php echo htmlspecialchars($old['phone']); ?>">
                    </div>
                    <div class="hg-hawker-form-group">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" minlength="6" required placeholder="Min. 6 characters">
                    </div>
                    <div class="hg-hawker-form-group">
                        <label for="cpassword">Confirm password</label>
                        <input id="cpassword" name="cpassword" type="password" minlength="6" required>
                    </div>
                </div>
                <button type="submit" name="submit" value="1" class="hg-btn hg-btn--gold btn-block">Create account</button>
            </form>

            <div class="hg-hawker-footer-links">
                <a href="login.php">Already have an account? Login</a>
                <br>
                <a href="index.php"><i class="fa fa-arrow-left"></i> Back to home</a>
            </div>
        </div>
    </section>
</div>
<script src="../js/jquery.min.js"></script>
<script src="../js/customer-auth-portal.js"></script>
<script>
(function () {
    var form = document.getElementById('createAccountForm');
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
