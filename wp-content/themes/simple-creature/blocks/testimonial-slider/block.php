<?php

/**
 * Testimonial Slider Block Template.
 */

// Support custom "anchor" values.
$id = $block['anchor'] ?? '';

// Create class attribute allowing for custom "className" values.
$classes = array_filter([
  'block-testimonial-slider',
  $block['className'] ?? '',
]);

$testimonials = get_field('testimonials');

?>
<div class="<?php echo implode(' ', $classes); ?>" id="<?php echo esc_attr($id); ?>">
  <?php if ($testimonials) : foreach ($testimonials as $testimonial) :  ?>
      <div class="testimonial">
        <blockquote class="animate-children">
          <?php echo apply_filters('the_content', get_post_field('post_content', $testimonial)); ?>
          <cite>
            <strong><?php echo get_the_title($testimonial); ?></strong><br />
            <?php echo get_field('title', $testimonial); ?>
          </cite>
        </blockquote>
      </div>
  <?php endforeach;
  endif; ?>
</div>
