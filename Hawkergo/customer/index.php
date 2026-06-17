<!DOCTYPE html>
<html lang="en">
<?php
include(__DIR__ . '/../connection/connect.php');
error_reporting(0);
session_start();

if (isset($_GET['clear_cart'])) {
    unset($_SESSION['cart_item']);
    header('Location: checkout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modal_add_to_cart'])) {
    $productId = intval($_POST['d_id']);
    $quantity = max(1, intval($_POST['quantity']));
    if ($productId > 0 && hawkergo_dish_available($db, $productId)) {
        $stmt = $db->prepare("SELECT d_id, title, price, rs_id FROM dishes WHERE d_id = ?");
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $productDetails = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($productDetails) {
            $itemArray = array(
                $productDetails['d_id'] => array(
                    'title' => $productDetails['title'],
                    'd_id' => $productDetails['d_id'],
                    'rs_id' => $productDetails['rs_id'],
                    'quantity' => $quantity,
                    'price' => $productDetails['price']
                )
            );
            if (!empty($_SESSION['cart_item'])) {
                if (in_array($productDetails['d_id'], array_keys($_SESSION['cart_item']))) {
                    $_SESSION['cart_item'][$productDetails['d_id']]['quantity'] += $quantity;
                } else {
                    $_SESSION['cart_item'] = $_SESSION['cart_item'] + $itemArray;
                }
            } else {
                $_SESSION['cart_item'] = $itemArray;
            }
        }
    }
    header('Location: checkout.php');
    exit;
}

$hawkerstalls = array();
$stall_res = mysqli_query($db, "SELECT rs_id, title, image FROM hawkerstalls ORDER BY title");
while ($row = mysqli_fetch_assoc($stall_res)) {
    $hawkerstalls[] = $row;
}

$menu_dishes = array();
$dish_res = mysqli_query($db, "SELECT d.d_id, d.rs_id, d.title, d.slogan, d.price, d.img, d.is_sold_out, r.title AS stall_name, r.accepting_orders FROM dishes d JOIN hawkerstalls r ON d.rs_id = r.rs_id ORDER BY r.title, d.title");
while ($row = mysqli_fetch_assoc($dish_res)) {
    $menu_dishes[] = $row;
}

$cart_items = !empty($_SESSION['cart_item']) ? $_SESSION['cart_item'] : array();
$cart_total = 0;
$cart_count = 0;
foreach ($cart_items as $item) {
    $cart_total += $item['price'] * $item['quantity'];
    $cart_count += $item['quantity'];
}

$open_modal = isset($_GET['open']) ? $_GET['open'] : '';
$is_logged_in = !empty($_SESSION['user_id']);
$stall_count = count($hawkerstalls);
$dish_count = count($menu_dishes);

$hero_stall = null;
foreach ($hawkerstalls as $stall_row) {
    if (!empty($stall_row['image'])) {
        $hero_stall = $stall_row;
        break;
    }
}
if (!$hero_stall && !empty($hawkerstalls)) {
    $hero_stall = $hawkerstalls[0];
}

$categories = array();
$cat_res = mysqli_query($db, "SELECT * FROM res_category ORDER BY c_name LIMIT 4");
if ($cat_res) {
    while ($cat_row = mysqli_fetch_assoc($cat_res)) {
        $categories[] = $cat_row;
    }
}
$cat_icons = array('fa-cutlery', 'fa-fire', 'fa-coffee', 'fa-leaf');
?>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">   
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="#">
    <title>Home</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/animsition.min.css" rel="stylesheet">
    <link href="../css/animate.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/theme.css" rel="stylesheet">
    <?php include __DIR__ . '/../includes/customer_styles.php'; ?>
    <link href="../css/home-modern.css" rel="stylesheet">
  </head>

<body class="home">

        <?php $nav_current = 'home'; include __DIR__ . '/../includes/customer_nav.php'; ?>

        <section class="hg-hero">
            <div class="container">
                <div class="hg-hero-grid">
                    <div class="hg-hero-copy">
                        <span class="hg-hero-eyebrow"><i class="fa fa-star"></i> HawkerGo · Order Online</span>
                        <h1>We Serve The Taste <span class="hg-hero-highlight">You Love</span> 😋</h1>
                        <p class="hg-hero-subtitle">Browse set meals from local hawker stalls, build your cart, choose dine-in or take-away, and collect with your queue number.</p>

                        <div class="hg-hero-actions">
                            <button type="button" class="hg-btn hg-btn--gold" data-toggle="modal" data-target="#browseMenuModal">
                                <i class="fa fa-cutlery"></i> Explore Food
                            </button>
                            <a href="hawkerstalls.php" class="hg-btn hg-btn--outline">
                                <i class="fa fa-search"></i> Find Stalls
                            </a>
                        </div>

                        <div class="hg-hero-reviews">
                            <span class="hg-hero-avatars"><i class="fa fa-user"></i><i class="fa fa-user"></i><i class="fa fa-user"></i></span>
                            <span class="hg-hero-review-text"><strong><?php echo max($stall_count, 1); ?>+ stalls</strong> · Queue-friendly pickup</span>
                            <span class="hg-hero-stars"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></span>
                        </div>
                    </div>

                    <div class="hg-hero-visual">
                        <span class="hg-float-badge hg-float-badge--top"><i class="fa fa-clock-o"></i> Queue on time</span>
                        <span class="hg-float-badge hg-float-badge--bottom"><i class="fa fa-tag"></i> Affordable prices</span>
                        <div class="hg-hero-circle">
                            <?php if ($hero_stall && !empty($hero_stall['image'])) { ?>
                                <img src="../admin/Res_img/<?php echo htmlspecialchars($hero_stall['image']); ?>" alt="<?php echo htmlspecialchars($hero_stall['title']); ?>">
                            <?php } else { ?>
                                <span class="hg-hero-circle-fallback"><i class="fa fa-store"></i></span>
                            <?php } ?>
                        </div>
                        <ul class="hg-hero-cats">
                            <li>
                                <a href="hawkerstalls.php">
                                    <span class="hg-hero-cat-thumb">
                                        <?php if ($hero_stall && !empty($hero_stall['image'])) { ?>
                                            <img src="../admin/Res_img/<?php echo htmlspecialchars($hero_stall['image']); ?>" alt="<?php echo htmlspecialchars($hero_stall['title']); ?>">
                                        <?php } else { ?>
                                            <i class="fa fa-store"></i>
                                        <?php } ?>
                                    </span>
                                    Stalls
                                </a>
                            </li>
                            <li><a href="#" data-toggle="modal" data-target="#browseMenuModal"><span><i class="fa fa-cutlery"></i></span>Dishes</a></li>
                            <li><a href="#" data-toggle="modal" data-target="#placeOrderModal"><span><i class="fa fa-shopping-bag"></i></span>Take-away</a></li>
                            <li><a href="checkout.php"><span><i class="fa fa-ticket"></i></span>My Cart<?php if ($cart_count > 0) { ?> <em><?php echo $cart_count; ?></em><?php } ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <?php if (!empty($categories)) { ?>
        <section class="hg-categories">
            <div class="container">
                <ul class="hg-cat-row">
                    <?php foreach ($categories as $i => $cat) {
                        $icon = $cat_icons[$i % count($cat_icons)];
                        $filter_class = str_replace(' ', '-', $cat['c_name']);
                    ?>
                    <li>
                        <a href="#stalls" class="hg-cat-item js-cat-filter" data-filter=".<?php echo htmlspecialchars($filter_class); ?>">
                            <span class="hg-cat-icon"><i class="fa <?php echo $icon; ?>"></i></span>
                            <?php echo htmlspecialchars($cat['c_name']); ?>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </div>
        </section>
        <?php } ?>
        <section class="hg-section hg-section-dishes">
            <div class="container">
                <div class="hg-section-head text-center">
                    <span class="hg-section-label">Menu highlights</span>
                    <h2>Popular Dishes</h2>
                    <p>Top picks this month — add to cart and checkout in minutes.</p>
                </div>
                <div class="row">
						<?php
						$query_res = mysqli_query($db, "SELECT * FROM dishes LIMIT 6");
						while ($r = mysqli_fetch_array($query_res)) {
                            $img = htmlspecialchars($r['img']);
                            $title = htmlspecialchars($r['title']);
                            $slogan = htmlspecialchars($r['slogan']);
                            $price = htmlspecialchars($r['price']);
                            $d_id = intval($r['d_id']);
                            $rs_id = intval($r['rs_id']);
                            $available = hawkergo_dish_available($db, $d_id);
                            $dish_rating = hawkergo_get_dish_rating_summary($db, $d_id);
                            $stars_html = $dish_rating['count'] > 0
                                ? hawkergo_render_star_rating($dish_rating['avg'], $dish_rating['count'])
                                : '<span class="hg-stars-count text-muted">No reviews yet</span>';
                            echo '<div class="col-xs-12 col-sm-6 col-md-4">
                                <div class="hg-dish-card">
                                    <div class="hg-dish-card-img-wrap">
                                        <div class="hg-dish-card-img" style="background-image:url(\'../admin/Res_img/dishes/' . $img . '\')"></div>
                                    </div>
                                    <div class="hg-dish-card-body">
                                        <div class="hg-dish-stars-wrap">' . $stars_html . '</div>
                                        <h5><a href="dishes.php?res_id=' . $rs_id . '">' . $title . '</a></h5>
                                        <p class="hg-dish-card-slogan">' . $slogan . '</p>
                                        <div class="hg-dish-card-footer">
                                            <span class="hg-dish-price">$' . $price . '</span>';
                            if ($available) {
                                echo '<form method="post" class="hg-dish-add-form">
                                            <input type="hidden" name="d_id" value="' . $d_id . '">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" name="modal_add_to_cart" class="hg-btn hg-btn--gold hg-btn--sm">Add To Cart</button>
                                        </form>';
                            } else {
                                echo '<span class="hg-dish-unavail">Unavailable</span>';
                            }
                            echo '    </div>
                                    </div>
                                </div>
                            </div>';
                        }
						?>
                </div>
                <div class="text-center hg-view-all-wrap">
                    <button type="button" class="hg-btn hg-btn--outline-dark" data-toggle="modal" data-target="#browseMenuModal">View All Menu</button>
                </div>
            </div>
        </section>

        <section class="hg-section hg-section-features">
            <div class="container">
                <div class="hg-features-grid">
                    <div class="hg-features-copy">
                        <span class="hg-section-label">Why HawkerGo</span>
                        <h2>More Than Just Ordering</h2>
                        <p>Dine-in, take-away, and queue-friendly pickup — everything you need from your favourite hawker stalls.</p>
                    </div>
                    <div class="hg-features-list">
                        <div class="hg-feature-item"><span><i class="fa fa-mobile"></i></span><strong>Online Order</strong><small>Order from any stall in one place</small></div>
                        <div class="hg-feature-item"><span><i class="fa fa-ticket"></i></span><strong>Queue Number</strong><small>Collect with your queue code</small></div>
                        <div class="hg-feature-item"><span><i class="fa fa-clock-o"></i></span><strong>Fast Pickup</strong><small>Dine-in or take-away options</small></div>
                        <div class="hg-feature-item"><span><i class="fa fa-shield"></i></span><strong>Secure Login</strong><small>Your cart is saved when you sign in</small></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="hg-section hg-section-alt hg-section-how">
            <div class="container">
                <div class="hg-section-head text-center">
                    <span class="hg-section-label">Simple &amp; fast</span>
                    <h2>How It Works</h2>
                    <p>Three simple steps from browsing to collecting your food.</p>
                </div>
                <div class="hg-how-grid">
                    <div class="hg-how-card step-clickable" role="button" tabindex="0" data-toggle="modal" data-target="#browseMenuModal">
                        <div class="hg-how-card-icon"><i class="fa fa-book"></i></div>
                        <span class="hg-how-item-step">Step 1</span>
                        <h3>Browse the Menu</h3>
                        <p>Explore dishes from multiple hawker stalls in one place.</p>
                    </div>
                    <div class="hg-how-card step-clickable" role="button" tabindex="0" data-toggle="modal" data-target="#placeOrderModal">
                        <div class="hg-how-card-icon"><i class="fa fa-shopping-basket"></i></div>
                        <span class="hg-how-item-step">Step 2</span>
                        <h3>Build Your Cart</h3>
                        <p>Add items, edit quantities, and choose dine-in or take-away.</p>
                    </div>
                    <div class="hg-how-card step-clickable" role="button" tabindex="0" data-toggle="modal" data-target="#makePaymentModal">
                        <div class="hg-how-card-icon"><i class="fa fa-ticket"></i></div>
                        <span class="hg-how-item-step">Step 3</span>
                        <h3>Pay &amp; Collect</h3>
                        <p>Login to pay and receive your queue number and verification code.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="hg-section hg-section-stalls" id="stalls">
            <div class="container">
                <div class="hg-stall-section-head">
                    <div>
                        <span class="hg-section-label">Explore</span>
                        <h2>Featured Hawker Stalls</h2>
                    </div>
                    <nav class="primary">
                        <ul class="hg-filter-pills">
                            <li><a href="#" class="selected" data-filter="*">All</a></li>
                            <?php
                            $res = mysqli_query($db, "SELECT * FROM res_category");
                            while ($row = mysqli_fetch_array($res)) {
                                $class_name = str_replace(' ', '-', $row['c_name']);
                                echo '<li><a href="#" data-filter=".' . htmlspecialchars($class_name) . '">' . htmlspecialchars($row['c_name']) . '</a></li>';
                            }
                            ?>
                        </ul>
                    </nav>
                </div>

                <div class="row hawkerstall-listing">
                    <?php
                    $ress = mysqli_query($db, "SELECT * FROM hawkerstalls");
                    while ($rows = mysqli_fetch_array($ress)) {
                        $query = mysqli_query($db, "SELECT * FROM res_category WHERE c_id='" . intval($rows['c_id']) . "'");
                        $rowss = mysqli_fetch_array($query);
                        $class_name = $rowss ? str_replace(' ', '-', $rowss['c_name']) : 'all';
                        $stall_url = 'dishes.php?res_id=' . intval($rows['rs_id']);
                        $stall_img = trim($rows['image']);
                        $stall_rating = hawkergo_get_stall_rating_summary($db, intval($rows['rs_id']));
                        echo '<div class="col-xs-12 col-sm-6 col-md-4 single-hawkerstall all ' . htmlspecialchars($class_name) . '">
                            <div class="hg-stall-card">
                                <a href="' . $stall_url . '" class="hg-stall-card-link">
                                    <div class="hg-stall-card-img">';
                        if ($stall_img !== '') {
                            echo '<img src="../admin/Res_img/' . htmlspecialchars($stall_img) . '" alt="' . htmlspecialchars($rows['title']) . '">';
                        } else {
                            echo '<span class="hg-stall-card-img-fallback"><i class="fa fa-store"></i></span>';
                        }
                        echo '      </div>
                                    <div class="hg-stall-card-body">
                                        <h5>' . htmlspecialchars($rows['title']) . '</h5>';
                        if ($stall_rating['count'] > 0) {
                            echo '<div class="hg-list-stall-rating">' . hawkergo_render_star_rating($stall_rating['avg'], $stall_rating['count']) . '</div>';
                        }
                        echo '          <span>' . htmlspecialchars($rows['address']) . '</span>
                                        <div class="hg-stall-card-cta">View menu <i class="fa fa-angle-right"></i></div>
                                    </div>
                                </a>
                            </div>
                        </div>';
                    }
                    ?>
                </div>
            </div>
        </section>
      
        <section class="hg-cta">
            <div class="container">
                <div class="hg-cta-inner">
                    <div class="hg-cta-copy">
                        <h2>Ready to order your hawker favourites?</h2>
                        <p>Browse the menu, add to cart, and collect with your queue number when you're ready.</p>
                    </div>
                    <div class="hg-cta-actions">
                        <button type="button" class="hg-btn hg-btn--gold hg-btn--lg" data-toggle="modal" data-target="#browseMenuModal">
                            <i class="fa fa-cutlery"></i> Order Now
                        </button>
                        <a href="hawkerstalls.php" class="hg-btn hg-btn--white">View All Stalls</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Browse Menu Modal -->
        <div class="modal fade hg-modal" id="browseMenuModal" tabindex="-1" role="dialog" aria-labelledby="browseMenuModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header hg-modal-header">
                        <div>
                            <h5 class="modal-title" id="browseMenuModalLabel"><i class="fa fa-book"></i> Browse Menu</h5>
                            <p class="hg-modal-subtitle">Explore dishes from our hawker stalls</p>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body hg-modal-body">
                        <?php if (empty($menu_dishes)) { ?>
                            <div class="hg-empty-state">
                                <i class="fa fa-cutlery"></i>
                                <p>No menu items available yet.</p>
                                <a href="hawkerstalls.php" class="btn btn-purple">View Hawker Stalls</a>
                            </div>
                        <?php } else { ?>
                            <ul class="nav nav-pills hg-menu-tabs" role="tablist">
                                <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#menu-all" role="tab">All Dishes</a></li>
                                <?php foreach ($hawkerstalls as $stall) { ?>
                                    <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#menu-<?php echo $stall['rs_id']; ?>" role="tab"><?php echo htmlspecialchars($stall['title']); ?></a></li>
                                <?php } ?>
                            </ul>
                            <div class="tab-content hg-menu-tab-content">
                                <div class="tab-pane fade in active" id="menu-all" role="tabpanel">
                                    <div class="hg-menu-grid">
                                        <?php foreach ($menu_dishes as $dish) {
                                            $unavailable = intval($dish['is_sold_out']) === 1 || intval($dish['accepting_orders']) === 0; ?>
                                            <div class="hg-menu-card<?php echo $unavailable ? ' hg-menu-card--disabled' : ''; ?>">
                                                <div class="hg-menu-card-img" style="background-image:url('../admin/Res_img/dishes/<?php echo htmlspecialchars($dish['img']); ?>')"></div>
                                                <div class="hg-menu-card-body">
                                                    <span class="hg-menu-stall"><?php echo htmlspecialchars($dish['stall_name']); ?></span>
                                                    <h6><?php echo htmlspecialchars($dish['title']); ?></h6>
                                                    <p><?php echo htmlspecialchars($dish['slogan']); ?></p>
                                                    <?php if (intval($dish['is_sold_out']) === 1) { ?><span class="badge badge-danger">Sold Out</span><?php } ?>
                                                    <div class="hg-menu-card-footer">
                                                        <span class="hg-price">$<?php echo htmlspecialchars($dish['price']); ?></span>
                                                        <?php if (!$unavailable) { ?>
                                                        <form method="post" class="hg-add-form">
                                                            <input type="hidden" name="d_id" value="<?php echo intval($dish['d_id']); ?>">
                                                            <input type="number" name="quantity" value="1" min="1" max="20" class="hg-qty-input">
                                                            <button type="submit" name="modal_add_to_cart" class="btn btn-sm btn-purple">Add</button>
                                                        </form>
                                                        <?php } else { ?><span class="text-muted small">Unavailable</span><?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php foreach ($hawkerstalls as $stall) { ?>
                                    <div class="tab-pane fade" id="menu-<?php echo $stall['rs_id']; ?>" role="tabpanel">
                                        <div class="hg-menu-grid">
                                            <?php foreach ($menu_dishes as $dish) {
                                                if (intval($dish['rs_id']) !== intval($stall['rs_id'])) continue;
                                                $unavailable = intval($dish['is_sold_out']) === 1 || intval($dish['accepting_orders']) === 0; ?>
                                                <div class="hg-menu-card<?php echo $unavailable ? ' hg-menu-card--disabled' : ''; ?>">
                                                    <div class="hg-menu-card-img" style="background-image:url('../admin/Res_img/dishes/<?php echo htmlspecialchars($dish['img']); ?>')"></div>
                                                    <div class="hg-menu-card-body">
                                                        <h6><?php echo htmlspecialchars($dish['title']); ?></h6>
                                                        <p><?php echo htmlspecialchars($dish['slogan']); ?></p>
                                                        <div class="hg-menu-card-footer">
                                                            <span class="hg-price">$<?php echo htmlspecialchars($dish['price']); ?></span>
                                                            <?php if (!$unavailable) { ?>
                                                            <form method="post" class="hg-add-form">
                                                                <input type="hidden" name="d_id" value="<?php echo intval($dish['d_id']); ?>">
                                                                <input type="number" name="quantity" value="1" min="1" max="20" class="hg-qty-input">
                                                                <button type="submit" name="modal_add_to_cart" class="btn btn-sm btn-purple">Add</button>
                                                            </form>
                                                            <?php } else { ?><span class="text-muted small">Unavailable</span><?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <div class="text-center mt-3">
                                            <a href="dishes.php?res_id=<?php echo $stall['rs_id']; ?>" class="btn btn-outline-purple">View Full Menu</a>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="modal-footer hg-modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                        <a href="checkout.php" class="btn btn-purple">Go to Your Cart</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Place Order Modal -->
        <div class="modal fade hg-modal" id="placeOrderModal" tabindex="-1" role="dialog" aria-labelledby="placeOrderModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header hg-modal-header hg-modal-header--accent">
                        <div>
                            <h5 class="modal-title" id="placeOrderModalLabel"><i class="fa fa-shopping-bag"></i> Place Order</h5>
                            <p class="hg-modal-subtitle">Review your cart and choose how you'd like to receive your food</p>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body hg-modal-body">
                        <?php if (empty($cart_items)) { ?>
                            <div class="hg-empty-state">
                                <i class="fa fa-shopping-cart"></i>
                                <p>Your cart is empty. Browse the menu to add items.</p>
                                <button type="button" class="btn btn-purple" data-dismiss="modal" data-toggle="modal" data-target="#browseMenuModal">Browse Menu</button>
                            </div>
                        <?php } else { ?>
                            <div class="hg-order-type">
                                <label class="hg-order-type-option">
                                    <input type="radio" name="modal_order_type" value="dine-in" class="js-modal-order-type" checked>
                                    <span class="hg-order-type-box"><i class="fa fa-cutlery"></i> Dine-In</span>
                                </label>
                                <label class="hg-order-type-option">
                                    <input type="radio" name="modal_order_type" value="takeaway" class="js-modal-order-type">
                                    <span class="hg-order-type-box"><i class="fa fa-shopping-bag"></i> Take-Away</span>
                                </label>
                            </div>
                            <div class="hg-cart-list">
                                <?php foreach ($cart_items as $item) { ?>
                                    <div class="hg-cart-row">
                                        <div class="hg-cart-info">
                                            <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                                            <span>Qty: <?php echo intval($item['quantity']); ?> &times; $<?php echo htmlspecialchars($item['price']); ?></span>
                                        </div>
                                        <span class="hg-price">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="hg-cart-summary">
                                <div class="hg-cart-summary-row"><span>Subtotal</span><span>$<?php echo number_format($cart_total, 2); ?></span></div>
                                <div class="hg-cart-summary-row"><span>Service type</span><span id="modal-order-type-label">Dine-In</span></div>
                                <div class="hg-cart-summary-row hg-cart-total"><span>Total</span><span>$<?php echo number_format($cart_total, 2); ?></span></div>
                            </div>
                            <p class="text-muted small mt-3"><i class="fa fa-info-circle"></i> Complete your order on the <a href="checkout.php">Your Cart</a> page.</p>
                        <?php } ?>
                    </div>
                    <div class="modal-footer hg-modal-footer">
                        <?php if (!empty($cart_items)) { ?>
                            <a href="index.php?clear_cart=1" class="btn btn-link text-danger" onclick="return confirm('Clear your cart?');">Clear Cart</a>
                        <?php } ?>
                        <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                        <?php if (!empty($cart_items)) { ?>
                            <a href="checkout.php" class="btn btn-purple">Go to Your Cart</a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Make Payment Modal -->
        <div class="modal fade hg-modal" id="makePaymentModal" tabindex="-1" role="dialog" aria-labelledby="makePaymentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header hg-modal-header hg-modal-header--gold">
                        <div>
                            <h5 class="modal-title" id="makePaymentModalLabel"><i class="fa fa-credit-card"></i> Make Payment</h5>
                            <p class="hg-modal-subtitle">Choose your payment method and confirm your order</p>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body hg-modal-body">
                        <?php if (empty($cart_items)) { ?>
                            <div class="hg-empty-state">
                                <i class="fa fa-credit-card"></i>
                                <p>Add items to your cart before making payment.</p>
                                <button type="button" class="btn btn-purple" data-dismiss="modal" data-toggle="modal" data-target="#browseMenuModal">Browse Menu</button>
                            </div>
                        <?php } else { ?>
                            <div class="hg-payment-methods">
                                <label class="hg-payment-option">
                                    <input type="radio" name="payment_method" value="cod" checked>
                                    <span class="hg-payment-box">
                                        <i class="fa fa-money"></i>
                                        <span>
                                            <strong>Cash on Pickup</strong>
                                            <small>Pay when you collect your order</small>
                                        </span>
                                    </span>
                                </label>
                                <label class="hg-payment-option">
                                    <input type="radio" name="payment_method" value="card">
                                    <span class="hg-payment-box">
                                        <i class="fa fa-credit-card"></i>
                                        <span>
                                            <strong>Credit / Debit Card</strong>
                                            <small>Visa, Mastercard, PayNow</small>
                                        </span>
                                    </span>
                                </label>
                                <label class="hg-payment-option hg-payment-option--disabled">
                                    <input type="radio" name="payment_method" value="paypal" disabled>
                                    <span class="hg-payment-box">
                                        <i class="fa fa-paypal"></i>
                                        <span>
                                            <strong>PayPal</strong>
                                            <small>Coming soon</small>
                                        </span>
                                    </span>
                                </label>
                            </div>
                            <div class="hg-payment-summary">
                                <h6>Order Summary</h6>
                                <?php foreach ($cart_items as $item) { ?>
                                    <div class="hg-cart-row hg-cart-row--compact">
                                        <span><?php echo htmlspecialchars($item['title']); ?> &times; <?php echo intval($item['quantity']); ?></span>
                                        <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                    </div>
                                <?php } ?>
                                <div class="hg-cart-summary-row hg-cart-total"><span>Amount Due</span><span>$<?php echo number_format($cart_total, 2); ?></span></div>
                            </div>
                            <?php if (!$is_logged_in) { ?>
                                <div class="alert alert-warning hg-login-notice">
                                    <i class="fa fa-lock"></i> Please <a href="login.php?return=checkout">login</a> to place your order. Your cart is saved.
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                    <div class="modal-footer hg-modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <?php if (!empty($cart_items)) { ?>
                            <a href="checkout.php" class="btn btn-purple btn-lg hg-pay-btn"><i class="fa fa-shopping-cart"></i> Go to Your Cart</a>
                        <?php } ?>
                    </div>
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
                        <?php if ($is_logged_in) { ?>
                            <a href="your_orders.php">My Orders</a>
                        <?php } else { ?>
                            <a href="login.php">Login</a>
                            <a href="create_account.php">Register</a>
                        <?php } ?>
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
    
    

    <script src="../js/jquery.min.js"></script>
    <script src="../js/tether.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/animsition.min.js"></script>
    <script src="../js/bootstrap-slider.min.js"></script>
    <script src="../js/jquery.isotope.min.js"></script>
    <script src="../js/headroom.js"></script>
    <script src="../js/foodpicky.min.js"></script>
    <script src="../js/home-modals.js"></script>
    <?php if ($open_modal === 'order') { ?>
    <script>window.HG_OPEN_MODAL = 'placeOrderModal';</script>
    <?php } elseif ($open_modal === 'payment') { ?>
    <script>window.HG_OPEN_MODAL = 'makePaymentModal';</script>
    <?php } elseif ($open_modal === 'menu') { ?>
    <script>window.HG_OPEN_MODAL = 'browseMenuModal';</script>
    <?php } ?>
</body>

</html>