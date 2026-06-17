<?php
session_start();
include('../connection/connect.php');
error_reporting(0);
hawkergo_require_hawker_login();

$hawker = hawkergo_get_logged_in_hawker($db);
$rs_id = intval($hawker['rs_id']);

$stats = array('preparing' => 0, 'completed' => 0, 'open_issues' => 0, 'today_revenue' => 0);

$stmt = $db->prepare("SELECT status, COUNT(*) AS cnt FROM users_orders WHERE rs_id = ? GROUP BY status");
$stmt->bind_param('i', $rs_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    if ($row['status'] === 'in process') {
        $stats['preparing'] = intval($row['cnt']);
    } elseif ($row['status'] === 'closed') {
        $stats['completed'] = intval($row['cnt']);
    }
}
$stmt->close();

$stmt = $db->prepare("SELECT COUNT(*) AS cnt FROM payment_issues WHERE rs_id = ? AND status = 'open'");
$stmt->bind_param('i', $rs_id);
$stmt->execute();
$stats['open_issues'] = intval($stmt->get_result()->fetch_assoc()['cnt']);
$stmt->close();

$stmt = $db->prepare(
    "SELECT COALESCE(SUM(price * quantity), 0) AS total
     FROM users_orders
     WHERE rs_id = ? AND status = 'closed' AND DATE(`date`) = CURDATE()"
);
$stmt->bind_param('i', $rs_id);
$stmt->execute();
$stats['today_revenue'] = floatval($stmt->get_result()->fetch_assoc()['total']);
$stmt->close();

$recent_orders = array();
$stmt = $db->prepare(
    "SELECT o_id, title, quantity, price, status, queue_number, `date`
     FROM users_orders WHERE rs_id = ? ORDER BY o_id DESC LIMIT 8"
);
$stmt->bind_param('i', $rs_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $recent_orders[] = $row;
}
$stmt->close();

$hg_hawker_page = 'dashboard';
$hg_hawker_title = 'Dashboard';
$hg_show_stall_hero = true;
include __DIR__ . '/includes/layout_start.php';
?>

<div class="hg-hawker-page-head">
    <h1>Stall Dashboard</h1>
    <p>Welcome back — manage orders, menu, and settings for your stall.</p>
</div>

<div class="hg-hawker-stats">
    <div class="hg-hawker-stat">
        <strong><?php echo $stats['preparing']; ?></strong>
        <span>Orders preparing</span>
    </div>
    <div class="hg-hawker-stat">
        <strong><?php echo $stats['completed']; ?></strong>
        <span>Completed orders</span>
    </div>
    <div class="hg-hawker-stat">
        <strong>$<?php echo number_format($stats['today_revenue'], 2); ?></strong>
        <span>Today's revenue</span>
    </div>
    <div class="hg-hawker-stat">
        <strong class="hg-hawker-open-toggle">
            <span class="hg-hawker-open-dot <?php echo intval($hawker['accepting_orders']) === 1 ? 'is-open' : 'is-closed'; ?>"></span>
            <?php echo intval($hawker['accepting_orders']) === 1 ? 'Open' : 'Closed'; ?>
        </strong>
        <span>Ordering status</span>
    </div>
</div>

<div class="hg-hawker-card">
    <h3><i class="fa fa-bolt"></i> Quick actions</h3>
    <div class="hg-hawker-actions">
        <a href="orders.php" class="hg-btn hg-btn--gold hg-btn--sm">View orders</a>
        <a href="menu.php" class="hg-btn hg-btn--outline-dark hg-btn--sm">Menu &amp; sold out</a>
        <a href="toggle_stall.php" class="hg-btn hg-btn--outline-dark hg-btn--sm">
            <?php echo intval($hawker['accepting_orders']) === 1 ? 'Close ordering' : 'Open ordering'; ?>
        </a>
        <a href="report_payment.php" class="hg-btn hg-btn--outline-dark hg-btn--sm">Report payment issue</a>
    </div>
</div>

<div class="hg-hawker-card">
    <h3><i class="fa fa-clock-o"></i> Recent customer orders</h3>
    <?php if (empty($recent_orders)) { ?>
        <div class="hg-hawker-empty"><i class="fa fa-inbox"></i><p>No orders yet for your stall.</p></div>
    <?php } else { ?>
        <div class="table-responsive">
            <table class="hg-hawker-table">
                <thead>
                    <tr><th>Order</th><th>Item</th><th>Qty</th><th>Queue</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($recent_orders as $order) { ?>
                    <tr>
                        <td>#<?php echo intval($order['o_id']); ?></td>
                        <td><?php echo htmlspecialchars($order['title']); ?></td>
                        <td><?php echo intval($order['quantity']); ?></td>
                        <td><?php echo !empty($order['queue_number']) ? intval($order['queue_number']) : '-'; ?></td>
                        <td><?php echo hawkergo_hawker_status_badge($order['status']); ?></td>
                        <td><a href="order_update.php?o_id=<?php echo intval($order['o_id']); ?>" class="hg-btn hg-btn--outline-dark hg-btn--sm">Update</a></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
