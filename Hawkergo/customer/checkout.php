<?php
include(__DIR__ . '/../connection/connect.php');
error_reporting(0);
session_start();

if (isset($_GET['clear_cart'])) {
    unset($_SESSION['cart_item']);
    header('Location: checkout.php');
    exit;
}

if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'remove') {
    $remove_id = intval($_GET['id']);
    if (!empty($_SESSION['cart_item'][$remove_id])) {
        unset($_SESSION['cart_item'][$remove_id]);
    }
    $return = isset($_GET['return']) ? $_GET['return'] : 'checkout.php';
    if (strpos($return, 'dishes.php') === 0 || $return === 'checkout.php') {
        header('Location: ' . $return);
    } else {
        header('Location: checkout.php');
    }
    exit;
}

$cart_items = !empty($_SESSION["cart_item"]) ? $_SESSION["cart_item"] : array();
$item_total = 0;
$error = '';
$login_notice = '';
$is_logged_in = !empty($_SESSION["user_id"]);
$selected_order_type = isset($_POST['order_type']) ? $_POST['order_type'] : 'takeaway';

if (!empty($_SESSION['checkout_login_notice'])) {
    $login_notice = $_SESSION['checkout_login_notice'];
    unset($_SESSION['checkout_login_notice']);
}

foreach ($cart_items as $item) {
    $item_total += ($item["price"] * $item["quantity"]);
}

$stall_names = !empty($cart_items) ? hawkergo_cart_stall_names($db, $cart_items) : array();
$cart_by_stall = !empty($cart_items) ? hawkergo_cart_items_by_stall($db, $cart_items) : array();
$cart_items = !empty($_SESSION['cart_item']) ? $_SESSION['cart_item'] : array();

if (isset($_POST['submit'])) {
    if (!$is_logged_in) {
        $_SESSION['checkout_login_notice'] = 'Please login to place your order. Your cart items are saved.';
        $_SESSION['redirect_after_login'] = 'checkout.php';
        header('Location: login.php');
        exit;
    }

    hawkergo_apply_cart_quantities(isset($_POST['qty']) ? $_POST['qty'] : array());
    $cart_items = !empty($_SESSION['cart_item']) ? $_SESSION['cart_item'] : array();
    $item_total = 0;
    foreach ($cart_items as $item) {
        $item_total += ($item['price'] * $item['quantity']);
    }

    if (empty($cart_items)) {
        $error = 'Your cart is empty.';
    } else {
        $options = array(
            'user_id' => $is_logged_in ? $_SESSION['user_id'] : 0,
            'guest_name' => isset($_POST['guest_name']) ? $_POST['guest_name'] : '',
            'guest_phone' => isset($_POST['guest_phone']) ? $_POST['guest_phone'] : '',
            'special_request' => isset($_POST['special_request']) ? $_POST['special_request'] : '',
            'payment_method' => isset($_POST['mod']) ? $_POST['mod'] : 'COD',
            'order_type' => isset($_POST['order_type']) ? $_POST['order_type'] : 'takeaway'
        );

        $result = hawkergo_place_cart_order($db, $cart_items, $options);

        if ($result['success']) {
            unset($_SESSION['cart_item']);

            $user_email = '';
            if ($is_logged_in) {
                $email_stmt = $db->prepare('SELECT email, phone FROM users WHERE u_id = ? LIMIT 1');
                $email_stmt->bind_param('i', $_SESSION['user_id']);
                $email_stmt->execute();
                $email_row = $email_stmt->get_result()->fetch_assoc();
                $email_stmt->close();
                if ($email_row) {
                    $user_email = trim($email_row['email']);
                }
            }

            $_SESSION['order_queue_number'] = $result['queue_number'];
            $_SESSION['order_verification_code'] = $result['verification_code'];
            $_SESSION['order_confirmation'] = array(
                'queue_number' => $result['queue_number'],
                'verification_code' => $result['verification_code'],
                'queues_by_stall' => isset($result['queues_by_stall']) ? $result['queues_by_stall'] : array(),
                'order_batch_id' => isset($result['order_batch_id']) ? $result['order_batch_id'] : '',
                'order_type' => $options['order_type'],
                'is_guest' => !empty($result['is_guest']),
                'guest_phone' => isset($options['guest_phone']) ? $options['guest_phone'] : '',
                'email' => $user_email
            );

            if (!empty($result['is_guest'])) {
                $_SESSION['guest_phone'] = $options['guest_phone'];
            }

            header('Location: order_thankyou.php');
            exit;
        }

        $error = $result['message'];
        $selected_order_type = $options['order_type'];
        $stall_names = hawkergo_cart_stall_names($db, $cart_items);
        $cart_by_stall = hawkergo_cart_items_by_stall($db, $cart_items);
    }
}

function hawkergo_order_type_label($type)
{
    return $type === 'dine-in' ? 'Dine-In' : 'Take-Away';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Your Cart - HawkerGo</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/theme.css" rel="stylesheet">
    <?php include __DIR__ . '/../includes/customer_styles.php'; ?>
    <style>
        .cart-page { padding-bottom: 48px; }
        .cart-card {
            background: #fff; border-radius: 18px; padding: 1.5rem;
            box-shadow: 0 18px 45px rgba(8,34,79,.08); margin-bottom: 1rem;
        }
        .cart-card h4 { color: #1f2e5f; font-weight: 700; margin-bottom: 1rem; }
        .cart-empty { text-align: center; padding: 3rem 1rem; }
        .cart-empty i { font-size: 3rem; color: #f5b800; margin-bottom: 1rem; }
        .order-type-summary {
            display: inline-flex; align-items: center; gap: .5rem;
            background: rgba(245,184,0,.18); color: #1f1f1f;
            padding: .45rem .85rem; border-radius: 999px; font-weight: 600;
        }
        .checkout-login-banner {
            background: rgba(245,184,0,.12);
            border: 1px solid rgba(245,184,0,.35);
            border-radius: 16px;
            padding: 1rem 1.25rem;
            margin-bottom: 1rem;
            color: #1f1f1f;
        }
        .checkout-login-banner strong { color: #ff8c00; }
        .checkout-login-card {
            background: #f8faff;
            border: 1px solid #e8edf7;
            border-radius: 16px;
            padding: 1.25rem;
        }
        .checkout-login-card h5 {
            color: #1f2e5f;
            font-weight: 700;
            margin: 0 0 .5rem;
        }
        .checkout-login-card p {
            color: #6b7794;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
<?php $nav_current = 'cart'; include __DIR__ . '/../includes/customer_nav.php'; ?>

<div class="page-wrapper">
<div class="container cart-page">
    <div class="mb-4">
        <h2 style="color:#1f2e5f;font-weight:700;margin-bottom:.35rem">Your Cart</h2>
        <p class="text-muted mb-0">Review items, choose dine-in or take-away, and complete payment — all on this page.</p>
    </div>

    <?php if (empty($cart_items)) { ?>
        <div class="cart-card cart-empty">
            <i class="fa fa-shopping-cart"></i>
            <h4>Your cart is empty</h4>
            <p class="text-muted">Browse the menu and add set meals to get started.</p>
            <a href="index.php?open=menu" class="btn btn-purple mr-2">Browse Menu</a>
            <a href="hawkerstalls.php" class="btn btn-outline-purple">View Hawker Stalls</a>
        </div>
    <?php } else { ?>
        <?php if ($login_notice) { ?>
            <div class="alert alert-warning checkout-login-banner">
                <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($login_notice); ?>
            </div>
        <?php } ?>
        <?php if (!$is_logged_in) { ?>
            <div class="checkout-login-banner">
                <i class="fa fa-lock"></i> <strong>Login required.</strong>
                You can add items to your cart as a guest, but you must login before placing an order.
            </div>
        <?php } ?>
        <?php if ($error) { ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php } ?>

        <form method="post" action="">
            <div class="row">
                <div class="col-lg-8">
                    <div class="cart-card js-cart-panel" data-cart-sync="cart_update.php">
                        <h4><i class="fa fa-shopping-basket"></i> Cart Items</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th><th></th></tr>
                                </thead>
                                <tbody>
                                <?php foreach ($cart_by_stall as $stall_group) { ?>
                                    <tr>
                                        <td colspan="5" class="stall-cart-group-title pt-3">
                                            <i class="fa fa-store"></i> <?php echo htmlspecialchars($stall_group['stall_name']); ?>
                                        </td>
                                    </tr>
                                    <?php foreach ($stall_group['items'] as $item) {
                                        $d_id = intval($item['d_id']);
                                        $line_subtotal = $item['price'] * $item['quantity'];
                                    ?>
                                    <tr class="js-cart-row" data-price="<?php echo htmlspecialchars($item['price']); ?>">
                                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                                        <td>
                                            <?php $qty = intval($item['quantity']); include __DIR__ . '/../includes/cart_qty_field.php'; ?>
                                        </td>
                                        <td>$<?php echo htmlspecialchars($item['price']); ?></td>
                                        <td class="cart-row-subtotal js-row-subtotal">$<?php echo number_format($line_subtotal, 2); ?></td>
                                        <td>
                                            <a href="checkout.php?action=remove&id=<?php echo $d_id; ?>" class="text-danger" title="Remove item" onclick="return confirm('Remove this item?');"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="cart-actions">
                            <a href="checkout.php?clear_cart=1" class="btn btn-link text-danger pl-0" onclick="return confirm('Clear entire cart?');">Clear cart</a>
                        </div>
                    </div>

                    <div class="cart-card">
                        <h4><i class="fa fa-cutlery"></i> Dine-In or Take-Away</h4>
                        <div class="hg-order-type">
                            <label class="hg-order-type-option">
                                <input type="radio" name="order_type" value="dine-in" class="js-order-type" <?php echo $selected_order_type === 'dine-in' ? 'checked' : ''; ?>>
                                <span class="hg-order-type-box"><i class="fa fa-cutlery"></i> Dine-In</span>
                            </label>
                            <label class="hg-order-type-option">
                                <input type="radio" name="order_type" value="takeaway" class="js-order-type" <?php echo $selected_order_type === 'takeaway' ? 'checked' : ''; ?>>
                                <span class="hg-order-type-box"><i class="fa fa-shopping-bag"></i> Take-Away</span>
                            </label>
                        </div>
                    </div>

                    <div class="cart-card">
                        <h4><i class="fa fa-pencil"></i> Special Requests</h4>
                        <textarea name="special_request" class="form-control" rows="3" placeholder="e.g. less spicy, no onions, extra sauce"><?php echo isset($_POST['special_request']) ? htmlspecialchars($_POST['special_request']) : ''; ?></textarea>
                    </div>

                    <?php if (!$is_logged_in) { ?>
                    <div class="cart-card checkout-login-card">
                        <h5><i class="fa fa-sign-in"></i> Login to checkout</h5>
                        <p>Sign in to pay and place your order. Your cart will stay saved until you login.</p>
                        <a href="login.php?return=checkout" class="btn btn-purple mr-2 js-go-login">Login</a>
                        <a href="create_account.php" class="btn btn-outline-purple">Create account</a>
                    </div>
                    <?php } ?>

                    <div class="cart-card">
                        <h4><i class="fa fa-credit-card"></i> Payment</h4>
                        <label class="custom-control custom-radio d-block mb-2">
                            <input type="radio" name="mod" value="COD" checked class="custom-control-input">
                            <span class="custom-control-indicator"></span> Cash / Card on Pickup
                        </label>
                        <label class="custom-control custom-radio d-block">
                            <input type="radio" name="mod" value="CARD" class="custom-control-input">
                            <span class="custom-control-indicator"></span> PayNow / Credit Card
                        </label>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="cart-card" style="position:sticky;top:100px">
                        <h4>Order Summary</h4>
                        <?php if (count($stall_names) === 1) { ?>
                            <p class="mb-2"><strong>Hawker Stall:</strong> <?php echo htmlspecialchars(reset($stall_names)); ?></p>
                        <?php } elseif (count($stall_names) > 1) { ?>
                            <p class="mb-2 text-muted small"><strong>Hawker Stalls:</strong> <?php echo htmlspecialchars(implode(', ', $stall_names)); ?></p>
                        <?php } ?>
                        <div class="hg-cart-summary" style="margin-top:0;padding:0;box-shadow:none">
                            <div class="hg-cart-summary-row"><span>Subtotal</span><span id="cart-subtotal">$<?php echo number_format($item_total, 2); ?></span></div>
                            <div class="hg-cart-summary-row">
                                <span>Service type</span>
                                <span class="order-type-summary" id="order-type-display">
                                    <i class="fa fa-shopping-bag"></i>
                                    <span id="order-type-label"><?php echo hawkergo_order_type_label($selected_order_type); ?></span>
                                </span>
                            </div>
                            <div class="hg-cart-summary-row hg-cart-total"><span>Total</span><span id="cart-total">$<?php echo number_format($item_total, 2); ?></span></div>
                        </div>
                        <p class="text-muted small mt-3">You will receive a queue number and verification code after payment.</p>
                        <?php if ($is_logged_in) { ?>
                        <button type="submit" name="submit" class="btn btn-purple btn-block btn-lg mt-2 js-place-order" onclick="return confirm('Confirm payment and place order?');">
                            Pay <span id="pay-amount">$<?php echo number_format($item_total, 2); ?></span> &amp; Place Order
                        </button>
                        <?php } else { ?>
                        <button type="button" class="btn btn-purple btn-block btn-lg mt-2 js-login-to-pay">
                            Login to Pay &amp; Place Order
                        </button>
                        <p class="text-muted small text-center mt-2 mb-0">Already ordered as guest? <a href="guest_orders.php">Track order</a></p>
                        <?php } ?>
                        <a href="index.php" class="btn btn-light btn-block mt-2">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </form>
    <?php } ?>
</div>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/cart-qty.js"></script>
<script src="../js/headroom.js"></script>
<script src="../js/customer-nav.js"></script>
<script>
(function () {
  var labels = { 'dine-in': 'Dine-In', 'takeaway': 'Take-Away' };
  var icons = { 'dine-in': 'fa-cutlery', 'takeaway': 'fa-shopping-bag' };

  function updateOrderTypeDisplay() {
    var val = document.querySelector('input[name="order_type"]:checked');
    if (!val) return;
    var label = document.getElementById('order-type-label');
    var icon = document.querySelector('#order-type-display i');
    if (label) label.textContent = labels[val.value] || val.value;
    if (icon) icon.className = 'fa ' + (icons[val.value] || 'fa-shopping-bag');
  }

  document.querySelectorAll('.js-order-type').forEach(function (el) {
    el.addEventListener('change', updateOrderTypeDisplay);
  });

  document.querySelectorAll('.js-login-to-pay, .js-go-login').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      if (btn.tagName === 'A') {
        e.preventDefault();
      }
      if (window.confirm('Please login to place your order.\n\nYour cart will be saved. Go to login now?')) {
        window.location.href = 'login.php?return=checkout';
      }
    });
  });
})();
</script>
</body>
</html>
