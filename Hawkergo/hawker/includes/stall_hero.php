<?php
if (empty($hawker) || empty($hawker['stall_name'])) {
    return;
}
$stall_name = htmlspecialchars($hawker['stall_name']);
$stall_img = !empty($hawker['image']) ? trim($hawker['image']) : '';
$is_open = intval($hawker['accepting_orders']) === 1;
?>
<section class="hg-hawker-stall-hero">
    <div class="hg-hawker-stall-hero-bg" aria-hidden="true"></div>
    <div class="hg-hawker-stall-hero-inner">
        <div class="hg-hawker-stall-hero-visual">
            <?php if ($stall_img !== '') { ?>
                <div class="hg-hawker-stall-hero-img">
                    <img src="../admin/Res_img/<?php echo htmlspecialchars($stall_img); ?>" alt="<?php echo $stall_name; ?>">
                </div>
            <?php } else { ?>
                <div class="hg-hawker-stall-hero-img hg-hawker-stall-hero-img--fallback">
                    <i class="fa fa-store"></i>
                </div>
            <?php } ?>
        </div>
        <div class="hg-hawker-stall-hero-copy">
            <span class="hg-hawker-stall-hero-label"><i class="fa fa-user"></i> Hawker</span>
            <h2 class="hg-hawker-stall-name">
                <?php echo hawkergo_render_animated_stall_name($hawker['stall_name'], array('size' => 'hero', 'tag' => 'span')); ?>
            </h2>
            <p class="hg-hawker-stall-hero-meta">
                <span class="hg-hawker-stall-status <?php echo $is_open ? 'is-open' : 'is-closed'; ?>">
                    <span class="hg-hawker-open-dot <?php echo $is_open ? 'is-open' : 'is-closed'; ?>"></span>
                    <?php echo $is_open ? 'Open for orders' : 'Not accepting orders'; ?>
                </span>
                <?php if (!empty($hawker['address'])) { ?>
                    <span><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($hawker['address']); ?></span>
                <?php } ?>
            </p>
        </div>
    </div>
</section>
