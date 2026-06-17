<?php
session_start();
include('../connection/connect.php');
error_reporting(0);

if (empty($_SESSION['adm_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_issue'])) {
    $issue_id = isset($_POST['issue_id']) ? intval($_POST['issue_id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : 'open';
    $admin_note = isset($_POST['admin_note']) ? trim($_POST['admin_note']) : '';
    $allowed = array('open', 'reviewing', 'resolved');

    if ($issue_id > 0 && in_array($status, $allowed, true)) {
        $stmt = $db->prepare('UPDATE payment_issues SET status = ?, admin_note = ? WHERE issue_id = ?');
        $stmt->bind_param('ssi', $status, $admin_note, $issue_id);
        $stmt->execute();
        $stmt->close();
        $message = 'Payment issue updated.';
    }
}

$issues = array();
$sql = "SELECT pi.*, hs.title AS stall_name, uo.title AS order_title, uo.payment_method, hu.username AS hawker_username
        FROM payment_issues pi
        LEFT JOIN hawkerstalls hs ON hs.rs_id = pi.rs_id
        LEFT JOIN users_orders uo ON uo.o_id = pi.o_id
        LEFT JOIN hawker_users hu ON hu.h_id = pi.h_id
        ORDER BY pi.issue_id DESC
        LIMIT 100";
$res = mysqli_query($db, $sql);
while ($row = mysqli_fetch_assoc($res)) {
    $issues[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Payment Issues - HawkerGo Admin</title>
    <link href="css/lib/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="css/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <?php include __DIR__ . '/includes/admin_styles.php'; ?>
</head>
<body class="fix-header hg-admin-portal">
<div id="main-wrapper">
    <?php $hg_admin_page = 'payment'; include __DIR__ . '/includes/admin_shell.php'; ?>
    <div class="page-wrapper">
        <div class="container-fluid">
            <?php if ($message) { ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php } ?>
            <div class="card card-outline-primary">
                <div class="card-header"><h4 class="m-b-0 text-white">Manage Payment Issues</h4></div>
                <div class="card-body">
                    <p class="text-muted">Reports submitted by hawkers about payment problems on customer orders.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th><th>Stall</th><th>Order</th><th>Payment</th><th>Description</th><th>Status</th><th>Update</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($issues)) { ?>
                                <tr><td colspan="7" class="text-center text-muted">No payment issues reported.</td></tr>
                            <?php } else { foreach ($issues as $issue) { ?>
                                <tr>
                                    <td>#<?php echo intval($issue['issue_id']); ?></td>
                                    <td><?php echo htmlspecialchars($issue['stall_name']); ?><br><small><?php echo htmlspecialchars($issue['hawker_username']); ?></small></td>
                                    <td>#<?php echo intval($issue['o_id']); ?> — <?php echo htmlspecialchars($issue['order_title']); ?></td>
                                    <td><?php echo htmlspecialchars($issue['payment_method'] ?: '-'); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($issue['description'])); ?></td>
                                    <td><?php echo htmlspecialchars($issue['status']); ?></td>
                                    <td>
                                        <form method="post" class="form-inline">
                                            <input type="hidden" name="issue_id" value="<?php echo intval($issue['issue_id']); ?>">
                                            <select name="status" class="form-control form-control-sm mr-1 mb-1">
                                                <option value="open" <?php echo $issue['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                                                <option value="reviewing" <?php echo $issue['status'] === 'reviewing' ? 'selected' : ''; ?>>Reviewing</option>
                                                <option value="resolved" <?php echo $issue['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                            </select>
                                            <input type="text" name="admin_note" class="form-control form-control-sm mr-1 mb-1" placeholder="Admin note" value="<?php echo htmlspecialchars($issue['admin_note']); ?>">
                                            <button type="submit" name="update_issue" value="1" class="btn btn-sm btn-primary mb-1">Save</button>
                                        </form>
                                    </td>
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
