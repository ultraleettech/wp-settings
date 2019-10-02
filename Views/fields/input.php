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
<label><?= $label ?>
    <input name="<?= $name ?>" <?= implode(' ', $inputAttributes) ?> value="<?= esc_attr($value) ?>">
</label>
