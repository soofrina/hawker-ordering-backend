<!DOCTYPE html>
<html lang="en">
<?php
include(__DIR__ . '/../connection/connect.php');
error_reporting(0);
session_start();

$res_id = isset($_GET['res_id']) ? intval($_GET['res_id']) : 0;

include_once 'product-action.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart']) && !empty($_POST['qty']) && is_array($_POST['qty'])) {
    hawkergo_apply_cart_quantities($_POST['qty']);
    header('Location: dishes.php?res_id=' . $res_id);
    exit;
}

$stall = null;
if ($res_id > 0) {
    $stmt = $db->prepare("SELECT * FROM hawkerstalls WHERE rs_id = ? LIMIT 1");
    $stmt->bind_param('i', $res_id);
    $stmt->execute();
    $stall = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (!$stall) {
    header('Location: hawkerstalls.php');
    exit;
}

$cart_items = !empty($_SESSION['cart_item']) ? $_SESSION['cart_item'] : array();
$stall_cart_items = hawkergo_cart_items_for_stall($db, $cart_items, $res_id);
$stall_cart_total = hawkergo_cart_total($stall_cart_items);
$full_cart_total = hawkergo_cart_total($cart_items);
$other_cart_count = count($cart_items) - count($stall_cart_items);
$cart_qty_by_dish = array();
foreach ($cart_items as $cart_item) {
    $cart_qty_by_dish[intval($cart_item['d_id'])] = intval($cart_item['quantity']);
}

$stall_open = intval($stall['accepting_orders']) === 1;
$stall_title = htmlspecialchars($stall['title']);
$stall_address = htmlspecialchars($stall['address']);
$stall_image = trim($stall['image']);

$dishes = array();
$dish_stmt = $db->prepare("SELECT * FROM dishes WHERE rs_id = ? ORDER BY title");
$dish_stmt->bind_param('i', $res_id);
$dish_stmt->execute();
$dish_res = $dish_stmt->get_result();
while ($row = $dish_res->fetch_assoc()) {
    $dishes[] = $row;
}
$dish_stmt->close();

$stall_rating = hawkergo_get_stall_rating_summary($db, $res_id);
$stall_reviews = hawkergo_get_stall_reviews($db, $res_id, 8);
$dish_reviews = hawkergo_get_stall_dish_reviews($db, $res_id, 8);
?>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $stall_title; ?> — Menu | HawkerGo</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/theme.css" rel="stylesheet">
    <?php include __DIR__ . '/../includes/customer_styles.php'; ?>
    <link href="../css/stall-page.css" rel="stylesheet">
</head>

<body>
    <?php $nav_current = 'hawkerstalls'; include __DIR__ . '/../includes/customer_nav.php'; ?>

    <div class="page-wrapper">
        <div class="stall-steps top-links">
            <div class="container">
                <ul class="row links">
                    <li class="col-xs-12 col-sm-4 link-item"><span>1</span><a href="hawkerstalls.php">Choose Hawker Stall</a></li>
                    <li class="col-xs-12 col-sm-4 link-item active"><span>2</span><a href="dishes.php?res_id=<?php echo $res_id; ?>">Pick Your Favourite Food</a></li>
                    <li class="col-xs-12 col-sm-4 link-item"><span>3</span><a href="checkout.php">Order and Pay</a></li>
                </ul>
            </div>
        </div>

        <section class="stall-hero">
            <div class="container">
                <div class="stall-hero-inner">
                    <div class="stall-hero-logo">
                        <?php if ($stall_image !== '') { ?>
                            <img src="../admin/Res_img/<?php echo htmlspecialchars($stall_image); ?>" alt="<?php echo $stall_title; ?>">
                        <?php } else { ?>
                            <span class="stall-hero-logo-fallback"><i class="fa fa-store"></i></span>
                        <?php } ?>
                    </div>
                    <div class="stall-hero-info">
                        <a href="hawkerstalls.php" class="stall-hero-back"><i class="fa fa-arrow-left"></i> All Hawker Stalls</a>
                        <h1><?php echo $stall_title; ?></h1>
                        <p class="stall-hero-address"><i class="fa fa-map-marker"></i> <?php echo $stall_address; ?></p>
                        <?php if ($stall_open) { ?>
                            <span class="stall-status-badge stall-status-badge--open"><i class="fa fa-check-circle"></i> Open for orders</span>
                        <?php } else { ?>
                            <span class="stall-status-badge stall-status-badge--closed"><i class="fa fa-times-circle"></i> Not accepting orders</span>
                        <?php } ?>
                        <?php if ($stall_rating['count'] > 0) { ?>
                            <div class="stall-hero-rating">
                                <?php echo hawkergo_render_star_rating($stall_rating['avg'], $stall_rating['count'], 'hg-stars hg-stars--lg'); ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </section>

        <div class="container stall-page-body">
            <div class="row">
                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-3">
                    <aside class="stall-cart-panel stall-sidebar-cart">
                        <div class="stall-cart-panel-head">
                            <h3><i class="fa fa-shopping-cart"></i> Your Cart</h3>
                            <p><?php echo $stall_title; ?></p>
                        </div>
                        <form method="post" action="dishes.php?res_id=<?php echo $res_id; ?>" class="js-cart-panel js-auto-save-cart">
                            <input type="hidden" name="update_cart" value="1">
                            <div class="stall-cart-panel-body">
                                <?php if (empty($stall_cart_items)) { ?>
                                    <div class="stall-cart-empty">
                                        <i class="fa fa-shopping-basket"></i>
                                        No items from this stall yet. Add dishes from the menu.
                                    </div>
                                <?php } else {
                                    foreach ($stall_cart_items as $item) {
                                        $d_id = intval($item['d_id']);
                                        $line_subtotal = $item['price'] * $item['quantity'];
                                ?>
                                    <div class="stall-cart-item js-cart-row" data-price="<?php echo htmlspecialchars($item['price']); ?>">
                                        <div class="stall-cart-item-title">
                                            <?php echo htmlspecialchars($item['title']); ?>
                                            <a href="dishes.php?res_id=<?php echo $res_id; ?>&action=remove&id=<?php echo $d_id; ?>" class="stall-cart-item-remove" title="Remove" onclick="return confirm('Remove this item?');">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                        <div class="stall-cart-item-meta">
                                            <span class="stall-cart-item-price">$<?php echo htmlspecialchars($item['price']); ?> each</span>
                                            <?php $qty = intval($item['quantity']); include __DIR__ . '/../includes/cart_qty_field.php'; ?>
                                        </div>
                                        <div class="stall-cart-item-sub">
                                            Subtotal: <strong class="js-row-subtotal">$<?php echo number_format($line_subtotal, 2); ?></strong>
                                        </div>
                                    </div>
                                <?php }
                                } ?>
                            </div>
                            <div class="stall-cart-panel-foot">
                                <p class="stall-cart-total-label">Stall subtotal</p>
                                <p class="stall-cart-total-value" id="stall-cart-total">$<?php echo number_format($stall_cart_total, 2); ?></p>
                                <?php if ($other_cart_count > 0) { ?>
                                    <p class="stall-cart-note">
                                        + <?php echo $other_cart_count; ?> item(s) from other stalls
                                        (<strong>$<?php echo number_format($full_cart_total - $stall_cart_total, 2); ?></strong>).
                                        <a href="checkout.php">View full cart</a>
                                    </p>
                                <?php } ?>
                                <p class="stall-cart-note">Choose Dine-In or Take-Away at checkout.</p>
                                <?php if ($full_cart_total == 0) { ?>
                                    <span class="stall-cart-checkout stall-cart-checkout--disabled">Checkout</span>
                                <?php } else { ?>
                                    <a href="checkout.php" class="stall-cart-checkout stall-cart-checkout--active">Go to Checkout</a>
                                <?php } ?>
                            </div>
                        </form>
                    </aside>
                </div>

                <div class="col-xs-12 col-sm-8 col-md-8 col-lg-9">
                    <div class="stall-menu-head">
                        <h2>Menu</h2>
                        <p>Browse set meals and add to your cart. Quantities auto-save in the sidebar.</p>
                    </div>

                    <?php if (!$stall_open) { ?>
                        <div class="stall-closed-banner">
                            <i class="fa fa-info-circle"></i> This stall is not accepting orders right now. You can still browse the menu.
                        </div>
                    <?php } ?>

                    <?php if (empty($dishes)) { ?>
                        <div class="stall-menu-empty">
                            <i class="fa fa-cutlery"></i>
                            <p>No dishes listed for this stall yet.</p>
                            <a href="hawkerstalls.php" class="hg-btn hg-btn--outline-dark">Browse Other Stalls</a>
                        </div>
                    <?php } else { ?>
                        <div class="stall-dish-grid">
                            <?php foreach ($dishes as $product) {
                                $d_id = intval($product['d_id']);
                                $available = hawkergo_dish_available($db, $d_id);
                                $sold_out = intval($product['is_sold_out']) === 1;
                                $in_cart = !empty($cart_qty_by_dish[$d_id]);
                                $img = htmlspecialchars($product['img']);
                                $dish_rating = hawkergo_get_dish_rating_summary($db, $d_id);
                                $card_class = 'stall-dish-card';
                                if (!$available) {
                                    $card_class .= ' stall-dish-card--disabled';
                                }
                            ?>
                            <div class="stall-dish-col">
                                <article class="<?php echo $card_class; ?>">
                                    <div class="stall-dish-img" style="background-image:url('../admin/Res_img/dishes/<?php echo $img; ?>')">
                                        <?php if ($sold_out) { ?>
                                            <span class="stall-dish-badge stall-dish-badge--sold">Sold out</span>
                                        <?php } elseif (!$stall_open) { ?>
                                            <span class="stall-dish-badge stall-dish-badge--closed">Stall closed</span>
                                        <?php } ?>
                                    </div>
                                    <div class="stall-dish-body">
                                        <h3><?php echo htmlspecialchars($product['title']); ?></h3>
                                        <?php if ($dish_rating['count'] > 0) { ?>
                                            <div class="hg-dish-stars-wrap"><?php echo hawkergo_render_star_rating($dish_rating['avg'], $dish_rating['count']); ?></div>
                                        <?php } ?>
                                        <p class="stall-dish-slogan"><?php echo htmlspecialchars($product['slogan']); ?></p>
                                        <?php if ($in_cart) { ?>
                                            <span class="stall-dish-in-cart"><i class="fa fa-shopping-cart"></i> In cart: <?php echo intval($cart_qty_by_dish[$d_id]); ?></span>
                                        <?php } ?>
                                        <div class="stall-dish-footer">
                                            <span class="stall-dish-price">$<?php echo htmlspecialchars($product['price']); ?></span>
                                            <?php if ($available) { ?>
                                            <form method="post" action="dishes.php?res_id=<?php echo $res_id; ?>&action=add&id=<?php echo $d_id; ?>" class="stall-dish-add">
                                                <input type="number" name="quantity" value="1" min="1" max="20" class="stall-dish-qty" aria-label="Quantity">
                                                <button type="submit" class="stall-dish-add-btn">Add To Cart</button>
                                            </form>
                                            <?php } else { ?>
                                                <span class="stall-dish-unavail"><?php echo $sold_out ? 'Sold out' : 'Unavailable'; ?></span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </article>
                            </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <section class="hg-review-section">
                        <h3><i class="fa fa-comments"></i> Customer Reviews</h3>
                        <ul class="nav hg-review-tabs" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#stallReviewsTab" role="tab">Stall (<?php echo count($stall_reviews); ?>)</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#dishReviewsTab" role="tab">Dishes (<?php echo count($dish_reviews); ?>)</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="stallReviewsTab" role="tabpanel">
                                <?php if (empty($stall_reviews)) { ?>
                                    <p class="hg-review-empty">No stall reviews yet. Order and collect your food, then leave a review from My Orders.</p>
                                <?php } else { ?>
                                    <div class="hg-review-list">
                                        <?php foreach ($stall_reviews as $rev) {
                                            $uname = !empty($rev['username']) ? htmlspecialchars($rev['username']) : 'Customer';
                                            $dt = !empty($rev['created_at']) ? date('d M Y', strtotime($rev['created_at'])) : '';
                                        ?>
                                        <div class="hg-review-card">
                                            <div class="hg-review-card-head">
                                                <span class="hg-review-card-user"><?php echo $uname; ?></span>
                                                <?php echo hawkergo_render_star_rating(intval($rev['rating'])); ?>
                                            </div>
                                            <?php if ($dt !== '') { ?><span class="hg-review-card-date"><?php echo $dt; ?></span><?php } ?>
                                            <?php if (!empty($rev['comment'])) { ?><p><?php echo nl2br(htmlspecialchars($rev['comment'])); ?></p><?php } ?>
                                        </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="tab-pane" id="dishReviewsTab" role="tabpanel">
                                <?php if (empty($dish_reviews)) { ?>
                                    <p class="hg-review-empty">No dish reviews yet for this stall.</p>
                                <?php } else { ?>
                                    <div class="hg-review-list">
                                        <?php foreach ($dish_reviews as $rev) {
                                            $uname = !empty($rev['username']) ? htmlspecialchars($rev['username']) : 'Customer';
                                            $dish_title = !empty($rev['dish_title']) ? htmlspecialchars($rev['dish_title']) : 'Dish';
                                            $dt = !empty($rev['created_at']) ? date('d M Y', strtotime($rev['created_at'])) : '';
                                        ?>
                                        <div class="hg-review-card">
                                            <div class="hg-review-card-head">
                                                <span class="hg-review-card-user"><?php echo $uname; ?> · <?php echo $dish_title; ?></span>
                                                <?php echo hawkergo_render_star_rating(intval($rev['rating'])); ?>
                                            </div>
                                            <?php if ($dt !== '') { ?><span class="hg-review-card-date"><?php echo $dt; ?></span><?php } ?>
                                            <?php if (!empty($rev['comment'])) { ?><p><?php echo nl2br(htmlspecialchars($rev['comment'])); ?></p><?php } ?>
                                        </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <?php if (!empty($_SESSION['user_id'])) { ?>
                            <p class="text-muted small mt-3 mb-0"><i class="fa fa-info-circle"></i> Leave reviews from <a href="your_orders.php">My Orders</a> after your order is collected.</p>
                        <?php } ?>
                    </section>
                </div>
            </div>
        </div>

        <footer class="hg-footer">
            <div class="container">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="hg-footer-brand"><?php include __DIR__ . '/../includes/hawkergo_logo.php'; ?></div>
                        <p>Order dine-in and take-away from your favourite hawker stalls — fast, simple, and queue-friendly.</p>
                    </div>
                    <div class="col-sm-4 hg-footer-links">
                        <h5>Quick Links</h5>
                        <a href="hawkerstalls.php">Hawker Stalls</a>
                        <a href="checkout.php">My Cart</a>
                        <a href="index.php">Home</a>
                    </div>
                    <div class="col-sm-4">
                        <h5>Contact</h5>
                        <p>9 Woodlands Avenue, Singapore<br>Phone: +65 8956 2321</p>
                    </div>
                </div>
                <div class="hg-footer-bottom">
                    &copy; <?php echo date('Y'); ?> HawkerGo. Cash &amp; card payments accepted on pickup.
                </div>
            </div>
        </footer>
    </div>

    <script src="../js/jquery.min.js"></script>
    <script src="../js/tether.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/cart-qty.js"></script>
    <script src="../js/headroom.js"></script>
    <script src="../js/foodpicky.min.js"></script>
</body>
</html>
