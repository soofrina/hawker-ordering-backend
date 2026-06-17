<?php
if (!isset($hg_admin_page)) {
    $hg_admin_page = '';
}
?>
<div class="header hg-admin-topbar">
    <nav class="navbar top-navbar navbar-expand-md navbar-light">
        <div class="navbar-header">
            <a class="navbar-brand hg-admin-brand-link" href="dashboard.php">
                <?php $hg_admin_brand_size = 'sm'; include __DIR__ . '/admin_brand.php'; ?>
            </a>
        </div>
        <div class="navbar-collapse">
            <ul class="navbar-nav mr-auto mt-md-0"></ul>
            <ul class="navbar-nav my-lg-0">
                <li class="nav-item">
                    <a class="nav-link hg-admin-logout-link" href="logout.php"><i class="fa fa-power-off"></i> Logout</a>
                </li>
            </ul>
        </div>
    </nav>
</div>
<div class="left-sidebar hg-admin-sidebar">
    <div class="scroll-sidebar">
        <?php include __DIR__ . '/admin_sidebar.php'; ?>
    </div>
</div>
