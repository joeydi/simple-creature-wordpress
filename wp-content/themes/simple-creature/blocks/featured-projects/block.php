<?php

/**
 * Featured Projects Block Template.
 */

// Support custom "anchor" values.
$id = $block['anchor'] ?? '';

// Create class attribute allowing for custom "className" values.
$classes = array_filter([
  'block-featured-projects',
  $block['className'] ?? '',
]);

$projects = get_field('projects');

?>
<div class="<?php echo implode(' ', $classes); ?>" id="<?php echo esc_attr($id); ?>">
  <?php if ($projects) : ?>
    <div class="slider">
      <?php foreach ($projects as $project) : ?>
        <a href="<?php echo get_the_permalink($project); ?>" class="excerpt">
          <h2 class="mb-10"><?php echo get_the_title($project); ?></h2>
          <p><?php echo get_field('teaser_caption', $project); ?></p>

          <picture class="aspect-video bg-black mt-30 mt-md-40">
            <?php echo wp_get_attachment_image(get_post_thumbnail_id($project), 'video-lg'); ?>
          </picture>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
