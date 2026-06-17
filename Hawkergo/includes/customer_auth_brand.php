<?php
/**
 * Animated HawkerGo wordmark for customer auth pages.
 * Optional: $hg_customer_brand_size = 'lg' | 'sm'
 */
$hg_customer_brand_size = isset($hg_customer_brand_size) ? $hg_customer_brand_size : 'lg';
$hg_customer_brand_letters = array(
    array('char' => 'H', 'color' => '#f5b800'),
    array('char' => 'a', 'color' => '#ff8c00'),
    array('char' => 'w', 'color' => '#e0a600'),
    array('char' => 'k', 'color' => '#f5b800'),
    array('char' => 'e', 'color' => '#ff8c00'),
    array('char' => 'r', 'color' => '#e0a600'),
    array('char' => 'G', 'color' => '#f5b800'),
    array('char' => 'o', 'color' => '#ff8c00'),
);
?>
<div class="hg-hawker-brand-showcase hg-hawker-brand-showcase--<?php echo htmlspecialchars($hg_customer_brand_size); ?>">
    <div class="hg-hawker-brand-mark" aria-hidden="true">
        <i class="fa fa-cutlery"></i>
        <span class="hg-hawker-brand-ring"></span>
    </div>
    <div class="hg-hawker-brand-text">
        <span class="hg-hawker-brand-word">
            <?php foreach ($hg_customer_brand_letters as $i => $letter) { ?>
                <span class="hg-hawker-brand-letter" style="--i: <?php echo (int) $i; ?>; --c: <?php echo $letter['color']; ?>;">
                    <?php echo htmlspecialchars($letter['char']); ?>
                </span>
            <?php } ?>
        </span>
        <span class="hg-hawker-brand-portal">Customer Account</span>
    </div>
</div>
