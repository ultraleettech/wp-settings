<?php
/**
 * @var string $label
 * @var string $name
 * @var array $value
 * @var array $attributes
 * @var array $options
 */

?>
<tr valign="top">
    <th scope="row" class="titledesc">
        <?= esc_html($label) ?>
    </th>
    <td class="forminp forminp-checkbox">
        <?php foreach ($options as $id => $title): ?>
        <div>
            <label>
                <input type="checkbox"
                       name="<?= $name ?>>"
                        <?= in_array($id, $value) ? 'checked' : '' ?>
                       value="<?= $id ?>">
                <?= esc_html($title) ?>
            </label>
        </div>
        <?php endforeach; ?>
    </td>
</tr>
