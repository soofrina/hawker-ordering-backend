<?php
/**
 * Animated "Hawker" wordmark for the hawker portal.
 * Optional: $hg_hawker_brand_size = 'lg' | 'sm'
 */
$hg_hawker_brand_size = isset($hg_hawker_brand_size) ? $hg_hawker_brand_size : 'lg';
$hg_hawker_brand_letters = array(
    array('char' => 'H', 'color' => '#f5b800'),
    array('char' => 'a', 'color' => '#ff8c00'),
    array('char' => 'w', 'color' => '#e0a600'),
    array('char' => 'k', 'color' => '#f5b800'),
    array('char' => 'e', 'color' => '#ff8c00'),
    array('char' => 'r', 'color' => '#e0a600'),
);
?>
<div class="hg-hawker-brand-showcase hg-hawker-brand-showcase--<?php echo htmlspecialchars($hg_hawker_brand_size); ?>">
    <div class="hg-hawker-brand-mark" aria-hidden="true">
        <i class="fa fa-store"></i>
        <span class="hg-hawker-brand-ring"></span>
    </div>
    <div class="hg-hawker-brand-text">
        <span class="hg-hawker-brand-word">
            <?php foreach ($hg_hawker_brand_letters as $i => $letter) { ?>
                <span class="hg-hawker-brand-letter" style="--i: <?php echo (int) $i; ?>; --c: <?php echo $letter['color']; ?>;">
                    <?php echo htmlspecialchars($letter['char']); ?>
                </span>
            <?php } ?>
        </span>
        <span class="hg-hawker-brand-portal">Portal</span>
    </div>
</div>
