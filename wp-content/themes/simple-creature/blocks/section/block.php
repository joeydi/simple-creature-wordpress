<?php

/**
 * Section Block Template.
 */

// Support custom "anchor" values.
$id = $block['anchor'] ?? '';

// Create class attribute allowing for custom "className" values.
$classes = array_filter([
  'full-width-block',
  'section-margin',
  $block['className'] ?? '',
  isset($block['textColor']) ? "text-{$block['textColor']}" : '',
  isset($block['backgroundColor']) ? "bg-{$block['backgroundColor']} has-background section-padding" : '',
]);

?>
<div class="<?php echo implode(' ', $classes); ?>" id="<?php echo esc_attr($id); ?>">
  <div class="container">
    <InnerBlocks />
  </div>
</div>
