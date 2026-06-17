<?php
session_start();
include('../connection/connect.php');
error_reporting(0);
hawkergo_require_hawker_login();

$hawker = hawkergo_get_logged_in_hawker($db);
$rs_id = intval($hawker['rs_id']);
$o_id = isset($_GET['o_id']) ? intval($_GET['o_id']) : 0;
$message = '';
$error = '';

if ($o_id <= 0 || !hawkergo_hawker_order_belongs_to_stall($db, $o_id, $rs_id)) {
    header('Location: orders.php');
    exit;
}

$stmt = $db->prepare(
    "SELECT o_id, title, quantity, price, status, queue_number, special_request, payment_method
     FROM users_orders WHERE o_id = ? AND rs_id = ? LIMIT 1"
);
$stmt->bind_param('ii', $o_id, $rs_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: orders.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $remark = isset($_POST['remark']) ? trim($_POST['remark']) : '';
    $allowed = array('in process', 'closed', 'rejected');

    if (!in_array($status, $allowed, true)) {
        $error = 'Please select a valid status.';
    } elseif ($remark === '') {
        $error = 'Please enter a remark.';
    } else {
        $stmt = $db->prepare('INSERT INTO remark (frm_id, status, remark) VALUES (?, ?, ?)');
        $stmt->bind_param('iss', $o_id, $status, $remark);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare('UPDATE users_orders SET status = ? WHERE o_id = ? AND rs_id = ?');
        $stmt->bind_param('sii', $status, $o_id, $rs_id);
        $stmt->execute();
        $stmt->close();

        $message = 'Order status updated successfully.';
        $order['status'] = $status;
    }
}

$hg_hawker_page = 'orders';
$hg_hawker_title = 'Update Order';
include __DIR__ . '/includes/layout_start.php';
?>

<div class="hg-hawker-page-head">
    <h1>Update Order #<?php echo intval($order['o_id']); ?></h1>
    <p><?php echo htmlspecialchars($order['title']); ?> · Qty <?php echo intval($order['quantity']); ?></p>
</div>

<div class="hg-hawker-card">
    <?php if ($message) { ?><div class="hg-hawker-alert hg-hawker-alert--success"><?php echo htmlspecialchars($message); ?></div><?php } ?>
    <?php if ($error) { ?><div class="hg-hawker-alert hg-hawker-alert--error"><?php echo htmlspecialchars($error); ?></div><?php } ?>

    <p><strong>Current status:</strong> <?php echo hawkergo_hawker_status_badge($order['status']); ?></p>
    <p><strong>Queue:</strong> <?php echo !empty($order['queue_number']) ? intval($order['queue_number']) : '-'; ?></p>
    <p><strong>Payment:</strong> <?php echo htmlspecialchars($order['payment_method'] ?: '-'); ?></p>
    <?php if (!empty($order['special_request'])) { ?>
        <p><strong>Special request:</strong> <?php echo htmlspecialchars($order['special_request']); ?></p>
    <?php } ?>

    <form method="post" action="">
        <div class="hg-hawker-form-group">
            <label for="status">New status</label>
            <select name="status" id="status" required>
                <option value="">Select status</option>
                <option value="in process" <?php echo $order['status'] === 'in process' ? 'selected' : ''; ?>>Preparing</option>
                <option value="closed" <?php echo $order['status'] === 'closed' ? 'selected' : ''; ?>>Completed / Ready for collection</option>
                <option value="rejected" <?php echo $order['status'] === 'rejected' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>
        <div class="hg-hawker-form-group">
            <label for="remark">Remark</label>
            <textarea name="remark" id="remark" required placeholder="e.g. Order is ready for pickup"></textarea>
        </div>
        <div class="hg-hawker-actions">
            <button type="submit" name="update" value="1" class="hg-btn hg-btn--gold">Save status</button>
            <a href="orders.php" class="hg-btn hg-btn--outline-dark">Back to orders</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
