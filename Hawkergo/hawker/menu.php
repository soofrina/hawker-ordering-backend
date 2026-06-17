<?php
session_start();
include('../connection/connect.php');
error_reporting(0);
hawkergo_require_hawker_login();

$hawker = hawkergo_get_logged_in_hawker($db);
$rs_id = intval($hawker['rs_id']);

$dishes = array();
$stmt = $db->prepare('SELECT d_id, title, price, slogan, is_sold_out FROM dishes WHERE rs_id = ? ORDER BY title');
$stmt->bind_param('i', $rs_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $dishes[] = $row;
}
$stmt->close();

$hg_hawker_page = 'menu';
$hg_hawker_title = 'Menu & Sold Out';
include __DIR__ . '/includes/layout_start.php';
?>

<div class="hg-hawker-page-head">
    <h1>Menu &amp; Sold Out</h1>
    <p>Toggle sold-out status for your set meals. Customers cannot order sold-out items.</p>
</div>

<div class="hg-hawker-card">
    <?php if (empty($dishes)) { ?>
        <div class="hg-hawker-empty"><i class="fa fa-cutlery"></i><p>No menu items linked to your stall yet.</p></div>
    <?php } else { ?>
        <div class="table-responsive">
            <table class="hg-hawker-table">
                <thead>
                    <tr><th>Dish</th><th>Price</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php foreach ($dishes as $dish) { ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($dish['title']); ?></strong>
                            <?php if (!empty($dish['slogan'])) { ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars($dish['slogan']); ?></small>
                            <?php } ?>
                        </td>
                        <td>$<?php echo htmlspecialchars($dish['price']); ?></td>
                        <td>
                            <?php if (intval($dish['is_sold_out']) === 1) { ?>
                                <span class="hg-badge hg-badge--danger">Sold out</span>
                            <?php } else { ?>
                                <span class="hg-badge hg-badge--success">Available</span>
                            <?php } ?>
                        </td>
                        <td>
                            <a href="toggle_soldout.php?d_id=<?php echo intval($dish['d_id']); ?>" class="hg-btn hg-btn--outline-dark hg-btn--sm">
                                <?php echo intval($dish['is_sold_out']) === 1 ? 'Mark available' : 'Mark sold out'; ?>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>

<?php include __DIR__ . '/includes/layout_end.php'; ?>
