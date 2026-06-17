<?php
session_start();
include("../connection/connect.php");
error_reporting(0);

if (empty($_SESSION['adm_id'])) {
    header('Location: index.php');
    exit;
}

$stalls = array();
$res = mysqli_query($db, "SELECT rs_id, title, address, o_hr, c_hr, o_days, accepting_orders FROM hawkerstalls ORDER BY title");
while ($row = mysqli_fetch_assoc($res)) {
    $stalls[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stall Control - HawkerGo Admin</title>
    <link href="css/lib/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="css/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <?php include __DIR__ . '/includes/admin_styles.php'; ?>
</head>
<body class="fix-header hg-admin-portal">
<div id="main-wrapper">
    <?php $hg_admin_page = 'stall_control'; include __DIR__ . '/includes/admin_shell.php'; ?>
    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="card card-outline-primary">
                <div class="card-header"><h4 class="m-b-0 text-white">Hawker Ordering Control</h4></div>
                <div class="card-body">
                    <p class="text-muted">Open or close ordering for each hawker stall. Fixed opening hours are shown for reference; use the toggle to accept or stop orders immediately.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Stall</th>
                                    <th>Address</th>
                                    <th>Opening Hours</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($stalls as $stall) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($stall['title']); ?></td>
                                    <td><?php echo htmlspecialchars($stall['address']); ?></td>
                                    <td><?php echo htmlspecialchars($stall['o_hr'] . ' - ' . $stall['c_hr']); ?></td>
                                    <td><?php echo htmlspecialchars($stall['o_days']); ?></td>
                                    <td>
                                        <?php if (intval($stall['accepting_orders']) === 1) { ?>
                                            <span class="badge badge-success">Open for Orders</span>
                                        <?php } else { ?>
                                            <span class="badge badge-danger">Closed</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <a href="toggle_stall.php?rs_id=<?php echo intval($stall['rs_id']); ?>" class="btn btn-sm <?php echo intval($stall['accepting_orders']) === 1 ? 'btn-warning' : 'btn-success'; ?>">
                                            <?php echo intval($stall['accepting_orders']) === 1 ? 'Close Ordering' : 'Open Ordering'; ?>
                                        </a>
                                        <a href="update_hawkerstall.php?res_upd=<?php echo intval($stall['rs_id']); ?>" class="btn btn-sm btn-info">Edit Hours</a>
                                    </td>
                                </tr>
                            <?php } ?>
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
