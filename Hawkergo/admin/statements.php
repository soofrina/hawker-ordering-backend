<?php
session_start();
include("../connection/connect.php");
error_reporting(0);

if (empty($_SESSION['adm_id'])) {
    header('Location: index.php');
    exit;
}

$period = isset($_GET['period']) ? $_GET['period'] : 'daily';
$rs_id = isset($_GET['rs_id']) ? intval($_GET['rs_id']) : 0;

$stalls = array();
$res = mysqli_query($db, "SELECT rs_id, title FROM hawkerstalls ORDER BY title");
while ($row = mysqli_fetch_assoc($res)) {
    $stalls[] = $row;
}

$where = "WHERE status IN ('closed', 'in process')";
if ($rs_id > 0) {
    $where .= " AND rs_id = " . $rs_id;
}

if ($period === 'monthly') {
    $group = "DATE_FORMAT(`date`, '%Y-%m')";
    $label = "Month";
} else {
    $group = "DATE(`date`)";
    $label = "Date";
    $period = 'daily';
}

$sql = "SELECT $group AS period_label,
               COUNT(DISTINCT order_batch_id) AS order_count,
               SUM(quantity) AS items_sold,
               SUM(price * quantity) AS revenue
        FROM users_orders
        $where
        GROUP BY period_label
        ORDER BY period_label DESC
        LIMIT 60";

$rows = array();
$query = mysqli_query($db, $sql);
if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        $rows[] = $row;
    }
}

$total_revenue = 0;
$total_orders = 0;
foreach ($rows as $row) {
    $total_revenue += floatval($row['revenue']);
    $total_orders += intval($row['order_count']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Statements - HawkerGo Admin</title>
    <link href="css/lib/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="css/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <?php include __DIR__ . '/includes/admin_styles.php'; ?>
</head>
<body class="fix-header hg-admin-portal">
<div id="main-wrapper">
    <?php $hg_admin_page = 'statements'; include __DIR__ . '/includes/admin_shell.php'; ?>
    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4"><div class="card p-3"><h4>$<?php echo number_format($total_revenue, 2); ?></h4><span>Total Revenue (shown periods)</span></div></div>
                <div class="col-md-4"><div class="card p-3"><h4><?php echo $total_orders; ?></h4><span>Total Orders</span></div></div>
                <div class="col-md-4"><div class="card p-3"><h4><?php echo ucfirst($period); ?></h4><span>Report Type</span></div></div>
            </div>
            <div class="card card-outline-primary m-t-20">
                <div class="card-header"><h4 class="m-b-0 text-white">Daily &amp; Monthly Account Statements</h4></div>
                <div class="card-body">
                    <form method="get" class="form-inline mb-4">
                        <label class="mr-2">Period</label>
                        <select name="period" class="form-control mr-3">
                            <option value="daily" <?php echo $period === 'daily' ? 'selected' : ''; ?>>Daily</option>
                            <option value="monthly" <?php echo $period === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                        </select>
                        <label class="mr-2">Stall</label>
                        <select name="rs_id" class="form-control mr-3">
                            <option value="0">All Stalls</option>
                            <?php foreach ($stalls as $stall) { ?>
                                <option value="<?php echo intval($stall['rs_id']); ?>" <?php echo $rs_id === intval($stall['rs_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($stall['title']); ?></option>
                            <?php } ?>
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr><th><?php echo $label; ?></th><th>Orders</th><th>Items Sold</th><th>Revenue</th></tr>
                            </thead>
                            <tbody>
                            <?php if (empty($rows)) { ?>
                                <tr><td colspan="4" class="text-center">No statement data yet.</td></tr>
                            <?php } else { foreach ($rows as $row) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['period_label']); ?></td>
                                    <td><?php echo intval($row['order_count']); ?></td>
                                    <td><?php echo intval($row['items_sold']); ?></td>
                                    <td>$<?php echo number_format($row['revenue'], 2); ?></td>
                                </tr>
                            <?php } } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="js/lib/jquery/jquery.min.js"></script>
<script src="js/lib/bootstrap/js/bootstrap.min.js"></script>
<script src="js/sidebarmenu.js"></script>
<?php include __DIR__ . '/includes/admin_scripts.php'; ?>
</body>
</html>
