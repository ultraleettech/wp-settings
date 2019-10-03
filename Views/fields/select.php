<?php
/**
 * @var string $label
 * @var string $name
 * @var string|array $value
 * @var array $attributes
 * @var array $options
 */

$selectAttributes = [];
foreach ($attributes as $attributeName => $attributeValue) {
    $attributeValue = esc_attr($attributeValue);
    $selectAttributes[] = "$attributeName=\"$attributeValue\"";
}?>
<tr valign="top">
    <th scope="row" class="titledesc">
        <label for="<?= $attributes['id'] ?>"><?= esc_html($label) ?></label>
    </th>
    <td class="forminp forminp-select">
        <select name="<?= $name ?>" <?= implode(' ', $selectAttributes) ?>>
            <?php foreach ($options as $id => $title): ?>
                <option value="<?= $id ?>"<?= in_array($id, (array) $value) ? ' selected' : '' ?>>
                    <?= esc_html($title) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </td>
</tr>
