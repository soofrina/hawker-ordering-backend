<?php
/**
 * Stall picker with photos — expects $stall_picker_stalls (array).
 * Optional: $stall_picker_select_available_only (bool, default false).
 */
if (!isset($stall_picker_stalls)) {
    $stall_picker_stalls = array();
}
if (!isset($stall_picker_select_available_only)) {
    $stall_picker_select_available_only = false;
}
?>
<div class="js-hawker-stall-picker hg-hawker-stall-picker" role="group" aria-label="Choose stall">
    <?php foreach ($stall_picker_stalls as $stall) {
        $taken = !empty($stall['taken']);
        $img_src = hawkergo_hawker_stall_image_src(isset($stall['image']) ? $stall['image'] : '');
        ?>
    <button type="button"
            class="hg-hawker-stall-pick-btn<?php echo $taken ? ' is-taken' : ''; ?>"
            data-rs-id="<?php echo intval($stall['rs_id']); ?>"
            data-stall-name="<?php echo htmlspecialchars($stall['title'], ENT_QUOTES, 'UTF-8'); ?>"
            data-stall-image="<?php echo htmlspecialchars($img_src, ENT_QUOTES, 'UTF-8'); ?>"
            <?php echo $taken ? 'disabled' : ''; ?>>
        <span class="hg-hawker-stall-pick-photo">
            <?php if ($img_src !== '') { ?>
                <img src="<?php echo htmlspecialchars($img_src); ?>" alt="<?php echo htmlspecialchars($stall['title']); ?>">
            <?php } else { ?>
                <span class="hg-hawker-stall-pick-photo-fallback"><i class="fa fa-store"></i></span>
            <?php } ?>
        </span>
        <span class="hg-hawker-stall-pick-body">
            <span class="hg-hawker-stall-pick-title"><?php echo htmlspecialchars($stall['title']); ?></span>
            <?php if (!empty($stall['address'])) { ?>
                <small><?php echo htmlspecialchars($stall['address']); ?></small>
            <?php } ?>
            <?php if ($taken) { ?><em>Already registered</em><?php } ?>
        </span>
    </button>
    <?php } ?>
</div>
<select id="rs_id" name="rs_id" required class="hg-hawker-stall-select">
    <option value="">— Select stall —</option>
    <?php foreach ($stall_picker_stalls as $stall) {
        if ($stall_picker_select_available_only && !empty($stall['taken'])) {
            continue;
        }
        $img_src = hawkergo_hawker_stall_image_src(isset($stall['image']) ? $stall['image'] : '');
        ?>
    <option value="<?php echo intval($stall['rs_id']); ?>"
            data-stall-image="<?php echo htmlspecialchars($img_src, ENT_QUOTES, 'UTF-8'); ?>">
        <?php echo htmlspecialchars($stall['title']); ?>
    </option>
    <?php } ?>
</select>
<div class="js-hawker-stall-preview hg-hawker-stall-preview" aria-live="polite"></div>
