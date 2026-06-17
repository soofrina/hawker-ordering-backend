<?php
session_start();
include('../connection/connect.php');
error_reporting(0);
hawkergo_require_hawker_login();

$hawker = hawkergo_get_logged_in_hawker($db);
$rs_id = intval($hawker['rs_id']);
$filter = isset($_GET['status']) ? $_GET['status'] : 'all';

$sql = "SELECT o_id, title, quantity, price, status, queue_number, verification_code,
               payment_method, order_type, special_request, `date`
        FROM users_orders WHERE rs_id = ?";
$params = array($rs_id);
$types = 'i';

if ($filter === 'preparing') {
    $sql .= " AND status = 'in process'";
} elseif ($filter === 'completed') {
    $sql .= " AND status = 'closed'";
} elseif ($filter === 'cancelled') {
    $sql .= " AND status = 'rejected'";
}

$sql .= ' ORDER BY o_id DESC LIMIT 100';

$stmt = $db->prepare($sql);
$stmt->bind_param($types, $rs_id);
$stmt->execute();
$res = $stmt->get_result();
$orders = array();
while ($row = $res->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

$hg_hawker_page = 'orders';
$hg_hawker_title = 'Customer Orders';
include __DIR__ . '/includes/layout_start.php';
?>

<div class="hg-hawker-page-head">
    <h1>Customer Orders</h1>
    <p>View and update order status for your stall.</p>
</div>

<div class="hg-hawker-card">
    <form method="get" class="hg-hawker-filter-bar">
        <div class="hg-hawker-form-group">
            <label>Filter status</label>
            <select name="status">
                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All</option>
                <option value="preparing" <?php echo $filter === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                <option value="completed" <?php echo $filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="cancelled" <?php echo $filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>
        <button type="submit" class="hg-btn hg-btn--gold hg-btn--sm">Apply</button>
    </form>

    <?php if (empty($orders)) { ?>
        <div class="hg-hawker-empty"><i class="fa fa-shopping-cart"></i><p>No orders found.</p></div>
    <?php } else { ?>
        <div class="table-responsive">
            <table class="hg-hawker-table">
                <thead>
                    <tr>
                        <th>ID</th><th>Item</th><th>Qty</th><th>Total</th><th>Queue</th>
                        <th>Payment</th><th>Status</th><th>Date</th><th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order) {
                    $line_total = floatval($order['price']) * intval($order['quantity']);
                ?>
                    <tr>
                        <td>#<?php echo intval($order['o_id']); ?></td>
                        <td><?php echo htmlspecialchars($order['title']); ?></td>
                        <td><?php echo intval($order['quantity']); ?></td>
                        <td>$<?php echo number_format($line_total, 2); ?></td>
                        <td><?php echo !empty($order['queue_number']) ? intval($order['queue_number']) : '-'; ?></td>
                        <td><?php echo htmlspecialchars($order['payment_method'] ?: '-'); ?></td>
                        <td><?php echo hawkergo_hawker_status_badge($order['status']); ?></td>
                        <td><?php echo htmlspecialchars($order['date']); ?></td>
                        <td><a href="order_update.php?o_id=<?php echo intval($order['o_id']); ?>" class="hg-btn hg-btn--outline-dark hg-btn--sm">Update</a></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
