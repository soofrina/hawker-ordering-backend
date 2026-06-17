<?php
if (!isset($hg_hawker_page)) {
    $hg_hawker_page = '';
}
if (!isset($hg_hawker_title)) {
    $hg_hawker_title = 'Hawker Portal';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($hg_hawker_title); ?> | HawkerGo</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/customer-bites.css" rel="stylesheet">
    <link href="../css/hawker-portal.css" rel="stylesheet">
</head>
<body class="hg-hawker-portal">
<div class="hg-hawker-wrap">
    <header class="hg-hawker-topbar">
        <div class="hg-hawker-topbar-inner">
            <a href="dashboard.php" class="hg-hawker-brand-link">
                <?php $hg_hawker_brand_size = 'sm'; include __DIR__ . '/hawker_brand.php'; ?>
            </a>
            <div class="hg-hawker-topbar-actions">
                <?php if (!empty($hawker)) { ?>
                    <span class="hg-hawker-stall-pill">
                        <i class="fa fa-store"></i>
                        <?php echo hawkergo_render_animated_stall_name($hawker['stall_name'], array('size' => 'pill', 'class' => 'hg-hawker-stall-pill-name')); ?>
                    </span>
                    <a href="logout.php" class="hg-btn hg-btn--outline-dark hg-btn--sm">Logout</a>
                <?php } ?>
            </div>
        </div>
    </header>

    <div class="hg-hawker-layout">
        <aside class="hg-hawker-sidebar">
            <?php if (!empty($hawker)) { ?>
            <div class="hg-hawker-sidebar-stall">
                <span class="hg-hawker-sidebar-stall-label"><i class="fa fa-user"></i> Hawker</span>
                <?php echo hawkergo_render_animated_stall_name($hawker['stall_name'], array('size' => 'sidebar', 'tag' => 'strong')); ?>
            </div>
            <?php } ?>
            <nav class="hg-hawker-nav">
                <a href="dashboard.php" class="<?php echo $hg_hawker_page === 'dashboard' ? 'active' : ''; ?>"><i class="fa fa-tachometer"></i> Dashboard</a>
                <a href="orders.php" class="<?php echo $hg_hawker_page === 'orders' ? 'active' : ''; ?>"><i class="fa fa-shopping-cart"></i> Customer Orders</a>
                <a href="menu.php" class="<?php echo $hg_hawker_page === 'menu' ? 'active' : ''; ?>"><i class="fa fa-cutlery"></i> Menu &amp; Sold Out</a>
                <a href="stall_settings.php" class="<?php echo $hg_hawker_page === 'settings' ? 'active' : ''; ?>"><i class="fa fa-cog"></i> Stall Settings</a>
                <a href="statements.php" class="<?php echo $hg_hawker_page === 'statements' ? 'active' : ''; ?>"><i class="fa fa-bar-chart"></i> Sales Reports</a>
                <a href="report_payment.php" class="<?php echo $hg_hawker_page === 'payment' ? 'active' : ''; ?>"><i class="fa fa-exclamation-circle"></i> Report Payment Issue</a>
                <a href="../customer/index.php"><i class="fa fa-home"></i> Customer Site</a>
            </nav>
        </aside>
        <main class="hg-hawker-main">
            <?php
            if (!empty($hg_show_stall_hero) && !empty($hawker)) {
                include __DIR__ . '/stall_hero.php';
            }
            ?>
