<?php
session_start();
include('../connection/connect.php');
error_reporting(0);
hawkergo_require_hawker_login();

$hawker = hawkergo_get_logged_in_hawker($db);
$rs_id = intval($hawker['rs_id']);
$h_id = intval($hawker['h_id']);
$message = '';
$error = '';

$order_options = array();
$stmt = $db->prepare(
    "SELECT o_id, title, quantity, price, status, queue_number, payment_method, `date`
     FROM users_orders WHERE rs_id = ? ORDER BY o_id DESC LIMIT 50"
);
$stmt->bind_param('i', $rs_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $order_options[] = $row;
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $o_id = isset($_POST['o_id']) ? intval($_POST['o_id']) : 0;
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    if ($o_id <= 0 || !hawkergo_hawker_order_belongs_to_stall($db, $o_id, $rs_id)) {
        $error = 'Please select a valid order from your stall.';
    } elseif ($description === '') {
        $error = 'Please describe the payment issue.';
    } else {
        $stmt = $db->prepare(
            'INSERT INTO payment_issues (o_id, rs_id, h_id, description, status) VALUES (?, ?, ?, ?, ?)'
        );
        $status = 'open';
        $stmt->bind_param('iiiss', $o_id, $rs_id, $h_id, $description, $status);
        if ($stmt->execute()) {
            $message = 'Payment issue reported. Admin will review it.';
        } else {
            $error = 'Could not submit report.';
        }
        $stmt->close();
    }
}

$recent_issues = array();
$stmt = $db->prepare(
    "SELECT pi.issue_id, pi.o_id, pi.description, pi.status, pi.admin_note, pi.created_at, uo.title
     FROM payment_issues pi
     LEFT JOIN users_orders uo ON uo.o_id = pi.o_id
     WHERE pi.rs_id = ?
     ORDER BY pi.issue_id DESC LIMIT 10"
);
$stmt->bind_param('i', $rs_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $recent_issues[] = $row;
}
$stmt->close();

$hg_hawker_page = 'payment';
$hg_hawker_title = 'Report Payment Issue';
include __DIR__ . '/includes/layout_start.php';
?>

<div class="hg-hawker-page-head">
    <h1>Report Payment Issue</h1>
    <p>Flag a customer order with a payment problem for admin review.</p>
</div>

<div class="hg-hawker-card">
    <?php if ($message) { ?><div class="hg-hawker-alert hg-hawker-alert--success"><?php echo htmlspecialchars($message); ?></div><?php } ?>
    <?php if ($error) { ?><div class="hg-hawker-alert hg-hawker-alert--error"><?php echo htmlspecialchars($error); ?></div><?php } ?>

    <form method="post" action="">
        <div class="hg-hawker-form-group">
            <label for="o_id">Order</label>
            <select name="o_id" id="o_id" required>
                <option value="">Select order</option>
                <?php foreach ($order_options as $order) { ?>
                    <option value="<?php echo intval($order['o_id']); ?>">
                        #<?php echo intval($order['o_id']); ?> — <?php echo htmlspecialchars($order['title']); ?>
                        (<?php echo htmlspecialchars($order['payment_method'] ?: 'N/A'); ?>)
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="hg-hawker-form-group">
            <label for="description">Issue description</label>
            <textarea name="description" id="description" required placeholder="e.g. Customer paid but order not reflected"></textarea>
        </div>
        <button type="submit" name="submit" value="1" class="hg-btn hg-btn--gold">Submit report</button>
    </form>
</div>

<div class="hg-hawker-card">
    <h3>Your recent reports</h3>
    <?php if (empty($recent_issues)) { ?>
        <p class="text-muted mb-0">No payment issues reported yet.</p>
    <?php } else { ?>
        <div class="table-responsive">
            <table class="hg-hawker-table">
                <thead>
                    <tr><th>ID</th><th>Order</th><th>Status</th><th>Date</th><th>Admin note</th></tr>
                </thead>
                <tbody>
                <?php foreach ($recent_issues as $issue) { ?>
                    <tr>
                        <td>#<?php echo intval($issue['issue_id']); ?></td>
                        <td>#<?php echo intval($issue['o_id']); ?> — <?php echo htmlspecialchars($issue['title']); ?></td>
                        <td><?php echo hawkergo_hawker_issue_status_badge($issue['status']); ?></td>
                        <td><?php echo htmlspecialchars($issue['created_at']); ?></td>
                        <td><?php echo !empty($issue['admin_note']) ? htmlspecialchars($issue['admin_note']) : '-'; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
