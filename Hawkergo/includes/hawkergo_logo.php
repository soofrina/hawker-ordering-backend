<?php
$hg_logo_letters = array(
    array('char' => 'H', 'color' => '#6b8cff'),
    array('char' => 'a', 'color' => '#f7b731'),
    array('char' => 'w', 'color' => '#4cd964'),
    array('char' => 'k', 'color' => '#ff6b81'),
    array('char' => 'e', 'color' => '#6b8cff'),
    array('char' => 'r', 'color' => '#f7b731'),
    array('char' => 'G', 'color' => '#4cd964'),
    array('char' => 'o', 'color' => '#ff6b81'),
);
?>
<span class="hg-logo" aria-hidden="true">
    <span class="hg-logo-mark">
        <i class="fa fa-cutlery"></i>
    </span>
    <span class="hg-logo-word">
        <?php foreach ($hg_logo_letters as $i => $letter) { ?>
            <span class="hg-logo-letter" style="--i: <?php echo (int) $i; ?>; --c: <?php echo $letter['color']; ?>;"><?php echo htmlspecialchars($letter['char']); ?></span>
        <?php } ?>
    </span>
</span>
