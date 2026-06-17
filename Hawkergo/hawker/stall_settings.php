<?php
session_start();
include('../connection/connect.php');
error_reporting(0);
hawkergo_require_hawker_login();

$hawker = hawkergo_get_logged_in_hawker($db);
$rs_id = intval($hawker['rs_id']);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $o_hr = isset($_POST['o_hr']) ? trim($_POST['o_hr']) : '';
    $c_hr = isset($_POST['c_hr']) ? trim($_POST['c_hr']) : '';
    $o_days = isset($_POST['o_days']) ? trim($_POST['o_days']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';

    $stmt = $db->prepare('UPDATE hawkerstalls SET o_hr = ?, c_hr = ?, o_days = ?, phone = ? WHERE rs_id = ?');
    $stmt->bind_param('ssssi', $o_hr, $c_hr, $o_days, $phone, $rs_id);
    if ($stmt->execute()) {
        $message = 'Stall settings saved.';
        $hawker = hawkergo_get_logged_in_hawker($db);
    } else {
        $error = 'Could not save settings.';
    }
    $stmt->close();
}

$hg_hawker_page = 'settings';
$hg_hawker_title = 'Stall Settings';
include __DIR__ . '/includes/layout_start.php';
?>

<div class="hg-hawker-page-head">
    <h1>Stall Settings</h1>
    <p>Update business hours and control whether your stall accepts orders.</p>
</div>

<div class="hg-hawker-card">
    <?php if ($message) { ?><div class="hg-hawker-alert hg-hawker-alert--success"><?php echo htmlspecialchars($message); ?></div><?php } ?>
    <?php if ($error) { ?><div class="hg-hawker-alert hg-hawker-alert--error"><?php echo htmlspecialchars($error); ?></div><?php } ?>

    <p class="mb-3">
        <strong>Ordering:</strong>
        <?php if (intval($hawker['accepting_orders']) === 1) { ?>
            <span class="hg-badge hg-badge--success">Open for orders</span>
        <?php } else { ?>
            <span class="hg-badge hg-badge--danger">Closed</span>
        <?php } ?>
        <span class="text-muted"> — </span>
        <a href="toggle_stall.php" class="hg-btn hg-btn--gold hg-btn--sm">
            <?php echo intval($hawker['accepting_orders']) === 1 ? 'Close ordering now' : 'Open ordering now'; ?>
        </a>
    </p>

    <form method="post" action="">
        <div class="row">
            <div class="col-md-6">
                <div class="hg-hawker-form-group">
                    <label for="o_hr">Opening hour</label>
                    <input type="text" id="o_hr" name="o_hr" value="<?php echo htmlspecialchars($hawker['o_hr']); ?>" placeholder="e.g. 7am">
                </div>
            </div>
            <div class="col-md-6">
                <div class="hg-hawker-form-group">
                    <label for="c_hr">Closing hour</label>
                    <input type="text" id="c_hr" name="c_hr" value="<?php echo htmlspecialchars($hawker['c_hr']); ?>" placeholder="e.g. 9pm">
                </div>
            </div>
        </div>
        <div class="hg-hawker-form-group">
            <label for="o_days">Operating days</label>
            <input type="text" id="o_days" name="o_days" value="<?php echo htmlspecialchars($hawker['o_days']); ?>" placeholder="e.g. Mon-Sat">
        </div>
        <div class="hg-hawker-form-group">
            <label for="phone">Contact phone</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($hawker['phone']); ?>">
        </div>
        <button type="submit" name="save" value="1" class="hg-btn hg-btn--gold">Save settings</button>
    </form>
</div>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
