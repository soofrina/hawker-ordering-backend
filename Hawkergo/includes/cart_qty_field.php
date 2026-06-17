<?php
if (!isset($d_id)) {
    $d_id = 0;
}
if (!isset($qty)) {
    $qty = 1;
}
?>
<div class="hg-cart-qty">
    <button type="button" class="hg-qty-btn js-qty-minus" aria-label="Decrease quantity">−</button>
    <input type="number" name="qty[<?php echo intval($d_id); ?>]" value="<?php echo intval($qty); ?>" min="1" max="99" class="hg-qty-input js-cart-qty" aria-label="Quantity">
    <button type="button" class="hg-qty-btn js-qty-plus" aria-label="Increase quantity">+</button>
</div>
