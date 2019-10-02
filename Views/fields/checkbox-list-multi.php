<?php
/**
 * @var string $label
 * @var string $name
 * @var array $value
 * @var array $attributes
 * @var array $options
 */

foreach ($options as $id => $title): ?>
<div>
    <input type="checkbox"
           name="<?= $name ?>>"
            <?= in_array($id, $value) ? 'checked' : '' ?>
           value="<?= $id ?>">
    <?= esc_html($title) ?>
</div>

<?php endforeach;
