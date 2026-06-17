<?php
if (!isset($hg_admin_page)) {
    $hg_admin_page = '';
}
?>
<nav class="sidebar-nav hg-admin-sidebar-nav">
    <ul id="sidebarnav">
        <li class="nav-devider"></li>
        <li class="nav-label">Home</li>
        <li><a href="dashboard.php" class="<?php echo $hg_admin_page === 'dashboard' ? 'active' : ''; ?>"><i class="fa fa-tachometer"></i><span>Dashboard</span></a></li>
        <li class="nav-label">Manage</li>
        <li><a href="all_users.php" class="<?php echo $hg_admin_page === 'users' ? 'active' : ''; ?>"><i class="fa fa-user"></i><span>Users</span></a></li>
        <li><a class="has-arrow <?php echo in_array($hg_admin_page, array('stalls', 'stalls_add', 'category'), true) ? 'active' : ''; ?>" href="#" aria-expanded="false"><i class="fa fa-archive"></i><span class="hide-menu">Hawker Stalls</span></a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="all_hawkerstalls.php">All Hawker Stalls</a></li>
                <li><a href="add_category.php">Add Category</a></li>
                <li><a href="add_hawkerstall.php">Add Hawker Stall</a></li>
            </ul>
        </li>
        <li><a class="has-arrow <?php echo in_array($hg_admin_page, array('menu', 'menu_add'), true) ? 'active' : ''; ?>" href="#" aria-expanded="false"><i class="fa fa-cutlery"></i><span class="hide-menu">Menu</span></a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="all_menu.php">All Menus</a></li>
                <li><a href="add_menu.php">Add Menu</a></li>
            </ul>
        </li>
        <li class="nav-label">Operations</li>
        <li><a href="all_orders.php" class="<?php echo $hg_admin_page === 'orders' ? 'active' : ''; ?>"><i class="fa fa-shopping-cart"></i><span>Orders</span></a></li>
        <li><a href="stall_control.php" class="<?php echo $hg_admin_page === 'stall_control' ? 'active' : ''; ?>"><i class="fa fa-toggle-on"></i><span>Ordering Control</span></a></li>
        <li><a href="payment_issues.php" class="<?php echo $hg_admin_page === 'payment' ? 'active' : ''; ?>"><i class="fa fa-exclamation-circle"></i><span>Payment Issues</span></a></li>
        <li><a href="statements.php" class="<?php echo $hg_admin_page === 'statements' ? 'active' : ''; ?>"><i class="fa fa-bar-chart"></i><span>Statements</span></a></li>
        <li class="nav-label">Links</li>
        <li><a href="../docs/index.html"><i class="fa fa-book"></i><span>User Guide</span></a></li>
        <li><a href="../hawker/index.php"><i class="fa fa-store"></i><span>Hawker Portal</span></a></li>
        <li><a href="../customer/index.php"><i class="fa fa-home"></i><span>Customer Site</span></a></li>
    </ul>
</nav>
