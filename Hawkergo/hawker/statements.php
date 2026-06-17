<?php
session_start();
include('../connection/connect.php');
error_reporting(0);
hawkergo_require_hawker_login();

$hawker = hawkergo_get_logged_in_hawker($db);
$rs_id = intval($hawker['rs_id']);
$period = isset($_GET['period']) ? $_GET['period'] : 'daily';
if ($period !== 'monthly') {
    $period = 'daily';
}

$group = $period === 'monthly' ? "DATE_FORMAT(`date`, '%Y-%m')" : 'DATE(`date`)';
$label = $period === 'monthly' ? 'Month' : 'Date';

$sql = "SELECT $group AS period_label,
               COUNT(DISTINCT order_batch_id) AS order_count,
               SUM(quantity) AS items_sold,
               SUM(price * quantity) AS revenue
        FROM users_orders
        WHERE rs_id = ? AND status IN ('closed', 'in process')
        GROUP BY period_label
        ORDER BY period_label DESC
        LIMIT 60";

$stmt = $db->prepare($sql);
$stmt->bind_param('i', $rs_id);
$stmt->execute();
$res = $stmt->get_result();
$rows = array();
while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
}
$stmt->close();

$total_revenue = 0;
$total_orders = 0;
foreach ($rows as $row) {
    $total_revenue += floatval($row['revenue']);
    $total_orders += intval($row['order_count']);
}

$hg_hawker_page = 'statements';
$hg_hawker_title = 'Sales Reports';
include __DIR__ . '/includes/layout_start.php';
?>

<div class="hg-hawker-page-head">
    <h1>Sales Reports</h1>
    <p>Daily and monthly revenue for <?php echo htmlspecialchars($hawker['stall_name']); ?>.</p>
</div>

<div class="hg-hawker-stats">
    <div class="hg-hawker-stat"><strong>$<?php echo number_format($total_revenue, 2); ?></strong><span>Total revenue (shown)</span></div>
    <div class="hg-hawker-stat"><strong><?php echo $total_orders; ?></strong><span>Total orders (shown)</span></div>
    <div class="hg-hawker-stat"><strong><?php echo ucfirst($period); ?></strong><span>Report type</span></div>
</div>

<div class="hg-hawker-card">
    <form method="get" class="hg-hawker-filter-bar">
        <div class="hg-hawker-form-group">
            <label>Period</label>
            <select name="period">
                <option value="daily" <?php echo $period === 'daily' ? 'selected' : ''; ?>>Daily</option>
                <option value="monthly" <?php echo $period === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
            </select>
        </div>
        <button type="submit" class="hg-btn hg-btn--gold hg-btn--sm">Filter</button>
    </form>

    <?php if (empty($rows)) { ?>
        <div class="hg-hawker-empty"><i class="fa fa-bar-chart"></i><p>No sales data yet.</p></div>
    <?php } else { ?>
        <div class="table-responsive">
            <table class="hg-hawker-table">
                <thead>
                    <tr><th><?php echo $label; ?></th><th>Orders</th><th>Items sold</th><th>Revenue</th></tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['period_label']); ?></td>
                        <td><?php echo intval($row['order_count']); ?></td>
                        <td><?php echo intval($row['items_sold']); ?></td>
                        <td>$<?php echo number_format(floatval($row['revenue']), 2); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
