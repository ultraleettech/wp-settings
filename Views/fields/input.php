<?php
/**
 * @var string $label
 * @var string $name
 * @var string $value
 * @var array $attributes
 */

$inputAttributes = [];
foreach ($attributes as $attributeName => $attributeValue) {
    $attributeValue = esc_attr($attributeValue);
    $inputAttributes[] = "$attributeName=\"$attributeValue\"";
}
?>
<tr valign="top">
    <th scope="row" class="titledesc">
        <label for="<?= $attributes['id'] ?>"><?= esc_html($label) ?></label>
    </th>
    <td class="forminp forminp-text">
        <input name="<?= $name ?>" <?= implode(' ', $inputAttributes) ?> value="<?= esc_attr($value) ?>">
    </td>
</tr>
