<!DOCTYPE html>
<html lang="en">
<?php
include(__DIR__ . '/../connection/connect.php');
error_reporting(0);
session_start();
?>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Hawker Stalls — HawkerGo</title>
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
                    <li class="col-xs-12 col-sm-4 link-item active"><span>1</span><a href="hawkerstalls.php">Choose Hawker Stall</a></li>
                    <li class="col-xs-12 col-sm-4 link-item"><span>2</span><a href="#">Pick Your Favourite Food</a></li>
                    <li class="col-xs-12 col-sm-4 link-item"><span>3</span><a href="checkout.php">Order and Pay</a></li>
                </ul>
            </div>
        </div>

        <section class="inner-page-hero">
            <div class="container text-center py-5">
                <h1 class="page-title">Hawker Stalls</h1>
                <p class="page-subtitle">Browse local hawker stalls, view their shop image, and start ordering from your favourite menu.</p>
            </div>
        </section>

        <section class="stalls-list-page">
            <div class="container">
                <div class="row stalls-list-grid">
                    <?php
                    $ress = mysqli_query($db, "SELECT * FROM hawkerstalls ORDER BY title");
                    while ($rows = mysqli_fetch_array($ress)) {
                        $rs_id = intval($rows['rs_id']);
                        $title = htmlspecialchars($rows['title']);
                        $address = htmlspecialchars($rows['address']);
                        $img = trim($rows['image']);
                        $open = intval($rows['accepting_orders']) === 1;
                        $stall_rating = hawkergo_get_stall_rating_summary($db, $rs_id);
                        $url = 'dishes.php?res_id=' . $rs_id;
                    ?>
                    <div class="col-xs-12 col-sm-6 col-md-4">
                        <div class="hg-list-stall-card">
                            <a href="<?php echo $url; ?>">
                                <div class="hg-list-stall-img">
                                    <?php if ($img !== '') { ?>
                                        <img src="../admin/Res_img/<?php echo htmlspecialchars($img); ?>" alt="<?php echo $title; ?>">
                                    <?php } else { ?>
                                        <span class="stall-hero-logo-fallback"><i class="fa fa-store"></i></span>
                                    <?php } ?>
                                </div>
                            </a>
                            <div class="hg-list-stall-body">
                                <h5><a href="<?php echo $url; ?>"><?php echo $title; ?></a></h5>
                                <?php if ($stall_rating['count'] > 0) { ?>
                                    <div class="hg-list-stall-rating"><?php echo hawkergo_render_star_rating($stall_rating['avg'], $stall_rating['count']); ?></div>
                                <?php } ?>
                                <p class="hg-list-stall-address"><i class="fa fa-map-marker"></i> <?php echo $address; ?></p>
                                <div class="hg-list-stall-meta">
                                    <?php if ($open) { ?>
                                        <span class="stall-status-badge stall-status-badge--open"><i class="fa fa-check-circle"></i> Open</span>
                                    <?php } else { ?>
                                        <span class="stall-status-badge stall-status-badge--closed"><i class="fa fa-times-circle"></i> Closed</span>
                                    <?php } ?>
                                    <a href="<?php echo $url; ?>" class="btn btn-purple btn-sm">View Menu</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </section>

        <footer class="hg-footer">
            <div class="container">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="hg-footer-brand"><?php include __DIR__ . '/../includes/hawkergo_logo.php'; ?></div>
                        <p>Order dine-in and take-away from your favourite hawker stalls — fast, simple, and queue-friendly.</p>
                    </div>
                    <div class="col-sm-4 hg-footer-links">
                        <h5>Quick Links</h5>
                        <a href="index.php">Home</a>
                        <a href="checkout.php">My Cart</a>
                        <a href="login.php">Login</a>
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
    <script src="../js/headroom.js"></script>
    <script src="../js/foodpicky.min.js"></script>
</body>
</html>

