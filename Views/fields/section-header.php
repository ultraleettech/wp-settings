<?php
/**
 * @var string $title
 * @var string $text
 */
?>
<?php if ($title): ?>
    <h4><?= $title ?></h4>
<?php endif; ?>
<?php if ($text): ?>
    <?= wpautop($text) ?>
<?php endif; ?>
