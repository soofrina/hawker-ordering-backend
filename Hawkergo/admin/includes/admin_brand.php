<?php
/**
 * Animated "Admin" wordmark — matches Hawker portal style.
 * Optional: $hg_admin_brand_size = 'lg' | 'sm'
 */
$hg_admin_brand_size = isset($hg_admin_brand_size) ? $hg_admin_brand_size : 'lg';
$hg_admin_brand_letters = array(
    array('char' => 'A', 'color' => '#f5b800'),
    array('char' => 'd', 'color' => '#ff8c00'),
    array('char' => 'm', 'color' => '#e0a600'),
    array('char' => 'i', 'color' => '#f5b800'),
    array('char' => 'n', 'color' => '#ff8c00'),
);
?>
<div class="hg-admin-brand-showcase hg-admin-brand-showcase--<?php echo htmlspecialchars($hg_admin_brand_size); ?>">
    <div class="hg-admin-brand-mark" aria-hidden="true">
        <i class="fa fa-shield"></i>
        <span class="hg-admin-brand-ring"></span>
    </div>
    <div class="hg-admin-brand-text">
        <span class="hg-admin-brand-word">
            <?php foreach ($hg_admin_brand_letters as $i => $letter) { ?>
                <span class="hg-admin-brand-letter" style="--i: <?php echo (int) $i; ?>; --c: <?php echo $letter['color']; ?>;">
                    <?php echo htmlspecialchars($letter['char']); ?>
                </span>
            <?php } ?>
        </span>
        <span class="hg-admin-brand-sub">HawkerGo Control</span>
    </div>
</div>
