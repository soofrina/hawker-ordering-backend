<?php
if (!isset($nav_current)) {
    $nav_current = '';
}
if (!isset($nav_wrap_header)) {
    $nav_wrap_header = true;
}

$hg_nav_logged_in = !empty($_SESSION['user_id']);
$hg_cart_count = 0;
if (!empty($_SESSION['cart_item']) && is_array($_SESSION['cart_item'])) {
    foreach ($_SESSION['cart_item'] as $cart_item) {
        $hg_cart_count += intval(isset($cart_item['quantity']) ? $cart_item['quantity'] : 0);
    }
}

function hg_nav_current_mark($page, $current)
{
    return $page === $current ? ' <span class="sr-only">(current)</span>' : '';
}

function hg_nav_link_class($page, $current)
{
    return 'nav-link' . ($page === $current ? ' active' : '');
}

function hg_cart_nav_label($count)
{
    $label = 'My Cart';
    if ($count > 0) {
        $label .= ' (' . $count . ')';
    }
    return $label;
}

if ($nav_wrap_header) {
    echo '<header id="header" class="header-scroll top-header headrom">';
}
?>
    <nav class="navbar navbar-light hg-customer-navbar">
        <div class="container">
            <button class="navbar-toggler hidden-lg-up" type="button" data-toggle="collapse" data-target="#mainNavbarCollapse">&#9776;</button>
            <a class="navbar-brand hg-logo-brand" href="index.php" aria-label="HawkerGo Home">
                <?php include __DIR__ . '/hawkergo_logo.php'; ?>
            </a>
            <div class="collapse navbar-toggleable-md float-lg-right" id="mainNavbarCollapse">
                <ul class="nav navbar-nav">
                    <li class="nav-item">
                        <a class="<?php echo hg_nav_link_class('home', $nav_current); ?>" href="index.php">Home<?php echo hg_nav_current_mark('home', $nav_current); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="<?php echo hg_nav_link_class('hawkerstalls', $nav_current); ?>" href="hawkerstalls.php">Hawker Stalls<?php echo hg_nav_current_mark('hawkerstalls', $nav_current); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="<?php echo hg_nav_link_class('cart', $nav_current); ?>" href="checkout.php"><?php echo htmlspecialchars(hg_cart_nav_label($hg_cart_count)); ?><?php echo hg_nav_current_mark('cart', $nav_current); ?></a>
                    </li>
                    <?php if ($hg_nav_logged_in) { ?>
                    <li class="nav-item">
                        <a class="<?php echo hg_nav_link_class('orders', $nav_current); ?>" href="your_orders.php">My Orders<?php echo hg_nav_current_mark('orders', $nav_current); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="<?php echo hg_nav_link_class('account', $nav_current); ?>" href="my_account.php">My Account<?php echo hg_nav_current_mark('account', $nav_current); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="<?php echo hg_nav_link_class('logout', $nav_current); ?>" href="logout.php">Logout<?php echo hg_nav_current_mark('logout', $nav_current); ?></a>
                    </li>
                    <?php } else { ?>
                    <li class="nav-item">
                        <a class="<?php echo hg_nav_link_class('login', $nav_current); ?>" href="login.php">Login<?php echo hg_nav_current_mark('login', $nav_current); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="<?php echo hg_nav_link_class('register', $nav_current); ?>" href="create_account.php">Register<?php echo hg_nav_current_mark('register', $nav_current); ?></a>
                    </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </nav>
<?php
if ($nav_wrap_header) {
    echo '</header>';
}
