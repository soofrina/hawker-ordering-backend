<?php
session_start();
include(__DIR__ . '/../connection/connect.php');
error_reporting(0);

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$uid = intval($_SESSION['user_id']);
$error = '';
$success = '';

if (!empty($_SESSION['account_success'])) {
    $success = $_SESSION['account_success'];
    unset($_SESSION['account_success']);
}
if (!empty($_SESSION['account_error'])) {
    $error = $_SESSION['account_error'];
    unset($_SESSION['account_error']);
}

function hawkergo_account_redirect($type, $message, $open_edit = false)
{
    $_SESSION['account_' . $type] = $message;
    header('Location: my_account.php' . ($open_edit ? '?edit=1' : ''));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $f_name = isset($_POST['f_name']) ? trim($_POST['f_name']) : '';
    $l_name = isset($_POST['l_name']) ? trim($_POST['l_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if ($username === '' || $f_name === '' || $l_name === '' || $email === '' || $phone === '') {
        hawkergo_account_redirect('error', 'Please fill in all required fields.', true);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        hawkergo_account_redirect('error', 'Please enter a valid email address.', true);
    }

    if (strlen($phone) < 8) {
        hawkergo_account_redirect('error', 'Please enter a valid phone number.', true);
    }

    $stmt = $db->prepare('SELECT password FROM users WHERE u_id = ? LIMIT 1');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $stmt->bind_result($stored_password);
    if (!$stmt->fetch()) {
        $stmt->close();
        hawkergo_account_redirect('error', 'Account not found.', true);
    }
    $stmt->close();

    $password_param = null;
    $changing_password = ($new_password !== '' || $confirm_password !== '' || $current_password !== '');

    if ($changing_password) {
        if ($new_password === '' || $confirm_password === '' || $current_password === '') {
            hawkergo_account_redirect('error', 'Enter your current password and new password to change it.', true);
        }
        if ($new_password !== $confirm_password) {
            hawkergo_account_redirect('error', 'New passwords do not match.', true);
        }
        if (strlen($new_password) < 6) {
            hawkergo_account_redirect('error', 'New password must be at least 6 characters.', true);
        }
        $password_ok = password_verify($current_password, $stored_password);
        if (!$password_ok && md5($current_password) === $stored_password) {
            $password_ok = true;
        }
        if (!$password_ok) {
            hawkergo_account_redirect('error', 'Current password is incorrect.', true);
        }
        $password_param = password_hash($new_password, PASSWORD_DEFAULT);
    }

    $stmt = $db->prepare('SELECT u_id FROM users WHERE username = ? AND u_id != ? LIMIT 1');
    $stmt->bind_param('si', $username, $uid);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        hawkergo_account_redirect('error', 'Username is already taken.', true);
    }
    $stmt->close();

    $stmt = $db->prepare('SELECT u_id FROM users WHERE email = ? AND u_id != ? LIMIT 1');
    $stmt->bind_param('si', $email, $uid);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        hawkergo_account_redirect('error', 'Email is already registered to another account.', true);
    }
    $stmt->close();

    if ($changing_password) {
        $stmt = $db->prepare('UPDATE users SET username=?, f_name=?, l_name=?, email=?, phone=?, address=?, password=? WHERE u_id=?');
        $stmt->bind_param('sssssssi', $username, $f_name, $l_name, $email, $phone, $address, $password_param, $uid);
    } else {
        $stmt = $db->prepare('UPDATE users SET username=?, f_name=?, l_name=?, email=?, phone=?, address=? WHERE u_id=?');
        $stmt->bind_param('ssssssi', $username, $f_name, $l_name, $email, $phone, $address, $uid);
    }

    if ($stmt && $stmt->execute()) {
        $stmt->close();
        hawkergo_account_redirect('success', 'Your account details have been updated.');
    }

    if ($stmt) {
        $stmt->close();
    }
    hawkergo_account_redirect('error', 'Unable to update account. Please try again.', true);
}

$stmt = $db->prepare('SELECT username, f_name, l_name, email, phone, address, date FROM users WHERE u_id = ? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: login.php');
    exit;
}

$order_count = 0;
$result = mysqli_query($db, "SELECT COUNT(*) AS cnt FROM users_orders WHERE u_id='$uid'");
if ($result && $row = mysqli_fetch_assoc($result)) {
    $order_count = intval($row['cnt']);
}

$full_name = trim($user['f_name'] . ' ' . $user['l_name']);
$initials = strtoupper(substr($user['f_name'], 0, 1) . substr($user['l_name'], 0, 1));
$member_since = date('F j, Y', strtotime($user['date']));
$user_address = isset($user['address']) ? trim($user['address']) : '';
$edit_mode = isset($_GET['edit']) || $error !== '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>My Account</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/theme.css" rel="stylesheet">
    <?php include __DIR__ . '/../includes/customer_styles.php'; ?>
    <style>
        .account-page { padding-bottom: 48px; }
        .account-hero {
            background: linear-gradient(135deg, #f5b800 0%, #e0a600 55%, #c99200 100%);
            border-radius: 22px;
            padding: 2rem 2rem 2.25rem;
            color: #1f1f1f;
            box-shadow: 0 22px 50px rgba(245, 184, 0, .22);
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        .account-hero::after {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
        }
        .account-avatar {
            width: 78px;
            height: 78px;
            border-radius: 20px;
            background: rgba(255, 255, 255, .18);
            border: 2px solid rgba(255, 255, 255, .35);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.65rem;
            font-weight: 700;
            letter-spacing: .05em;
            margin-bottom: 1rem;
        }
        .account-hero h1 {
            margin: 0 0 .35rem;
            font-size: 1.85rem;
            font-weight: 700;
        }
        .account-hero p {
            margin: 0;
            opacity: .92;
            font-size: .98rem;
        }
        .account-badge {
            display: inline-block;
            margin-top: 1rem;
            padding: .35rem .85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .16);
            font-size: .82rem;
            font-weight: 600;
        }
        .account-stat {
            background: #fff;
            border-radius: 18px;
            padding: 1.25rem 1.35rem;
            box-shadow: 0 14px 36px rgba(8, 34, 79, .07);
            margin-bottom: 1rem;
            text-align: center;
        }
        .account-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(245, 184, 0, .2);
            color: #ff8c00;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-bottom: .65rem;
        }
        .account-stat strong {
            display: block;
            font-size: 1.6rem;
            color: #1f1f1f;
            line-height: 1.1;
        }
        .account-stat span {
            color: #6b7794;
            font-size: .88rem;
        }
        .account-panel {
            background: #fff;
            border-radius: 20px;
            padding: 1.75rem;
            box-shadow: 0 18px 45px rgba(8, 34, 79, .08);
            margin-bottom: 1.5rem;
        }
        .account-panel-title {
            color: #1f1f1f;
            font-weight: 700;
            font-size: 1.15rem;
            margin: 0 0 1.25rem;
        }
        .account-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
        }
        .account-panel-head .account-panel-title {
            margin: 0;
        }
        .account-edit-btn {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            background: linear-gradient(135deg, #f5b800, #e0a600);
            border: none;
            color: #fff;
            font-weight: 700;
            border-radius: 12px;
            padding: .65rem 1.15rem;
            font-size: .92rem;
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .account-edit-btn:hover,
        .account-edit-btn:focus {
            color: #fff;
            text-decoration: none;
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(245, 184, 0, .28);
        }
        .account-cancel-btn {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            background: #fff;
            border: 1px solid #dbe3f3;
            color: #5b6b8a;
            font-weight: 600;
            border-radius: 12px;
            padding: .85rem 1.25rem;
            text-decoration: none;
        }
        .account-cancel-btn:hover,
        .account-cancel-btn:focus {
            color: #1f1f1f;
            text-decoration: none;
            border-color: #c9d4ff;
        }
        .account-form-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .65rem;
            margin-top: 1.5rem;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }
        .detail-item {
            background: #fdf8ee;
            border: 1px solid #e8edf7;
            border-radius: 16px;
            padding: 1rem 1.1rem;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .detail-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(8, 34, 79, .06);
        }
        .detail-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #fff;
            color: #ff8c00;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: .65rem;
            box-shadow: 0 4px 12px rgba(245, 184, 0, .15);
        }
        .detail-label {
            display: block;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #8a96ad;
            font-weight: 600;
            margin-bottom: .25rem;
        }
        .detail-value {
            color: #1f1f1f;
            font-size: 1rem;
            font-weight: 600;
            word-break: break-word;
        }
        .action-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .action-list li { margin-bottom: .75rem; }
        .action-list li:last-child { margin-bottom: 0; }
        .action-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .95rem 1.1rem;
            border-radius: 14px;
            background: #fdf8ee;
            border: 1px solid #e8edf7;
            color: #1f1f1f;
            text-decoration: none;
            font-weight: 600;
            transition: all .2s ease;
        }
        .action-link:hover,
        .action-link:focus {
            text-decoration: none;
            color: #3856d4;
            background: #fff;
            border-color: #c9d4ff;
            box-shadow: 0 8px 22px rgba(82, 104, 245, .12);
        }
        .action-link i:first-child {
            color: #ff8c00;
            margin-right: .65rem;
            width: 18px;
            text-align: center;
        }
        .action-link span {
            display: flex;
            align-items: center;
        }
        .action-link-danger { color: #d9534f; }
        .action-link-danger i:first-child { color: #d9534f; }
        .action-link-danger:hover { color: #c9302c; border-color: #f5c6cb; }
        .account-alert {
            border-radius: 12px;
            padding: .85rem 1rem;
            margin-bottom: 1.25rem;
            font-size: .92rem;
        }
        .account-alert-success {
            background: #f0fff4;
            border: 1px solid #b7ebc9;
            color: #2d8a4e;
        }
        .account-alert-error {
            background: #fff5f5;
            border: 1px solid #f5c6cb;
            color: #c9302c;
        }
        .account-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }
        .account-form-grid .full-width {
            grid-column: 1 / -1;
        }
        .account-field label {
            display: block;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #8a96ad;
            font-weight: 600;
            margin-bottom: .4rem;
        }
        .account-field .form-control {
            border: 1px solid #dbe3f3;
            border-radius: 12px;
            min-height: 44px;
            box-shadow: none;
            color: #1f1f1f;
        }
        .account-field .form-control:focus {
            border-color: #5268f5;
            box-shadow: 0 0 0 3px rgba(82, 104, 245, .12);
        }
        .account-field-readonly {
            background: #fdf8ee;
            border: 1px solid #e8edf7;
            border-radius: 12px;
            padding: .75rem 1rem;
            color: #1f1f1f;
            font-weight: 600;
        }
        .account-form-divider {
            border: 0;
            border-top: 1px solid #e8edf7;
            margin: 1.5rem 0;
        }
        .account-form-note {
            color: #6b7794;
            font-size: .88rem;
            margin: 0 0 1rem;
        }
        .account-save-btn {
            background: linear-gradient(135deg, #f5b800, #e0a600);
            border: none;
            color: #fff;
            font-weight: 700;
            border-radius: 12px;
            padding: .85rem 1.5rem;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .account-save-btn:hover,
        .account-save-btn:focus {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(245, 184, 0, .28);
        }
        @media (max-width: 767px) {
            .detail-grid { grid-template-columns: 1fr; }
            .account-form-grid { grid-template-columns: 1fr; }
            .account-hero { padding: 1.5rem; }
            .account-hero h1 { font-size: 1.45rem; }
        }
    </style>
</head>
<body>
<?php $nav_current = 'account'; include __DIR__ . '/../includes/customer_nav.php'; ?>

<div class="page-wrapper">
<div class="container account-page">
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1 col-md-12">

            <div class="account-hero">
                <div class="account-avatar"><?php echo htmlspecialchars($initials); ?></div>
                <h1><?php echo htmlspecialchars($full_name); ?></h1>
                <p>@<?php echo htmlspecialchars($user['username']); ?> · HawkerGo member</p>
                <span class="account-badge"><i class="fa fa-calendar"></i> Member since <?php echo htmlspecialchars($member_since); ?></span>
            </div>

            <div class="row">
                <div class="col-sm-4">
                    <div class="account-stat">
                        <div class="account-stat-icon"><i class="fa fa-shopping-bag"></i></div>
                        <strong><?php echo $order_count; ?></strong>
                        <span>Orders placed</span>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="account-stat">
                        <div class="account-stat-icon"><i class="fa fa-envelope"></i></div>
                        <strong style="font-size:1rem;padding-top:.35rem;">Active</strong>
                        <span>Email verified account</span>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="account-stat">
                        <div class="account-stat-icon"><i class="fa fa-phone"></i></div>
                        <strong style="font-size:1rem;padding-top:.35rem;">Linked</strong>
                        <span>Contact on file</span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="account-panel">
                        <div class="account-panel-head">
                            <h2 class="account-panel-title"><i class="fa fa-id-card-o"></i> Account Details</h2>
                            <?php if (!$edit_mode) { ?>
                                <a href="my_account.php?edit=1" class="account-edit-btn">
                                    <i class="fa fa-pencil"></i> Edit Account
                                </a>
                            <?php } ?>
                        </div>

                        <?php if ($success !== '') { ?>
                            <div class="account-alert account-alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php } ?>
                        <?php if ($error !== '') { ?>
                            <div class="account-alert account-alert-error"><?php echo htmlspecialchars($error); ?></div>
                        <?php } ?>

                        <?php if (!$edit_mode) { ?>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-icon"><i class="fa fa-user"></i></div>
                                <span class="detail-label">Username</span>
                                <span class="detail-value"><?php echo htmlspecialchars($user['username']); ?></span>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon"><i class="fa fa-envelope-o"></i></div>
                                <span class="detail-label">Email</span>
                                <span class="detail-value"><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon"><i class="fa fa-user-o"></i></div>
                                <span class="detail-label">First name</span>
                                <span class="detail-value"><?php echo htmlspecialchars($user['f_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon"><i class="fa fa-user-o"></i></div>
                                <span class="detail-label">Last name</span>
                                <span class="detail-value"><?php echo htmlspecialchars($user['l_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon"><i class="fa fa-phone"></i></div>
                                <span class="detail-label">Phone</span>
                                <span class="detail-value"><?php echo htmlspecialchars($user['phone']); ?></span>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon"><i class="fa fa-calendar-check-o"></i></div>
                                <span class="detail-label">Member since</span>
                                <span class="detail-value"><?php echo htmlspecialchars($member_since); ?></span>
                            </div>
                            <?php if ($user_address !== '') { ?>
                            <div class="detail-item" style="grid-column:1/-1;">
                                <div class="detail-icon"><i class="fa fa-map-marker"></i></div>
                                <span class="detail-label">Address</span>
                                <span class="detail-value"><?php echo htmlspecialchars($user_address); ?></span>
                            </div>
                            <?php } ?>
                        </div>
                        <?php } else { ?>
                        <form method="post" action="my_account.php" novalidate>
                            <input type="hidden" name="update_profile" value="1">
                            <div class="account-form-grid">
                                <div class="account-field">
                                    <label for="username">Username</label>
                                    <input id="username" name="username" type="text" class="form-control" required value="<?php echo htmlspecialchars($user['username']); ?>">
                                </div>
                                <div class="account-field">
                                    <label for="email">Email</label>
                                    <input id="email" name="email" type="email" class="form-control" required value="<?php echo htmlspecialchars($user['email']); ?>">
                                </div>
                                <div class="account-field">
                                    <label for="f_name">First name</label>
                                    <input id="f_name" name="f_name" type="text" class="form-control" required value="<?php echo htmlspecialchars($user['f_name']); ?>">
                                </div>
                                <div class="account-field">
                                    <label for="l_name">Last name</label>
                                    <input id="l_name" name="l_name" type="text" class="form-control" required value="<?php echo htmlspecialchars($user['l_name']); ?>">
                                </div>
                                <div class="account-field">
                                    <label for="phone">Phone</label>
                                    <input id="phone" name="phone" type="tel" class="form-control" required value="<?php echo htmlspecialchars($user['phone']); ?>">
                                </div>
                                <div class="account-field">
                                    <label>Member since</label>
                                    <div class="account-field-readonly"><?php echo htmlspecialchars($member_since); ?></div>
                                </div>
                                <div class="account-field full-width">
                                    <label for="address">Address</label>
                                    <input id="address" name="address" type="text" class="form-control" value="<?php echo htmlspecialchars($user_address); ?>" placeholder="Optional delivery or contact address">
                                </div>
                            </div>

                            <hr class="account-form-divider">
                            <h3 class="account-panel-title" style="font-size:1rem;margin-bottom:.65rem;"><i class="fa fa-lock"></i> Change password</h3>
                            <p class="account-form-note">Leave blank to keep your current password.</p>
                            <div class="account-form-grid">
                                <div class="account-field full-width">
                                    <label for="current_password">Current password</label>
                                    <input id="current_password" name="current_password" type="password" class="form-control" autocomplete="current-password">
                                </div>
                                <div class="account-field">
                                    <label for="new_password">New password</label>
                                    <input id="new_password" name="new_password" type="password" class="form-control" autocomplete="new-password">
                                </div>
                                <div class="account-field">
                                    <label for="confirm_password">Confirm new password</label>
                                    <input id="confirm_password" name="confirm_password" type="password" class="form-control" autocomplete="new-password">
                                </div>
                            </div>

                            <div class="account-form-actions">
                                <button type="submit" class="account-save-btn">
                                    <i class="fa fa-check"></i> Update Account
                                </button>
                                <a href="my_account.php" class="account-cancel-btn">Cancel</a>
                            </div>
                        </form>
                        <?php } ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="account-panel">
                        <h2 class="account-panel-title"><i class="fa fa-bolt"></i> Quick Actions</h2>
                        <ul class="action-list">
                            <li>
                                <a href="my_account.php?edit=1" class="action-link">
                                    <span><i class="fa fa-pencil"></i> Edit Account</span>
                                    <i class="fa fa-angle-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="your_orders.php" class="action-link">
                                    <span><i class="fa fa-list-alt"></i> My Orders</span>
                                    <i class="fa fa-angle-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="checkout.php" class="action-link">
                                    <span><i class="fa fa-shopping-cart"></i> My Cart</span>
                                    <i class="fa fa-angle-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="hawkerstalls.php" class="action-link">
                                    <span><i class="fa fa-store"></i> Browse Stalls</span>
                                    <i class="fa fa-angle-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="logout.php" class="action-link action-link-danger">
                                    <span><i class="fa fa-sign-out"></i> Logout</span>
                                    <i class="fa fa-angle-right"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/headroom.js"></script>
<script src="../js/customer-nav.js"></script>
</body>
</html>
