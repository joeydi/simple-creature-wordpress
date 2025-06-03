<?php

$page_for_posts = get_post( get_option( 'page_for_posts' ) );
$next_posts_link = get_next_posts_link( 'Load More Stories' );

get_header();

?>


<?php $post = $page_for_posts; get_template_part( 'partials/hero' ); wp_reset_postdata(); ?>

<section class="section-margin">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between animate-children">
                    <?php wp_nav_menu( [ 'menu' => 'blog', 'container' => false ] ); ?>
                    <?php get_search_form(); ?>
                </div>
            </div>
            <div class="animate-children col-md-10 col-lg-11 col-xl-10">
                <h1><?php the_archive_title(); ?></h1>
            </div>
        </div>
    </div>
</section>

<section class="blog">
    <div class="container">
        <div class="row posts">
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <?php $category = get_the_category(); $category = is_array( $category ) ? reset( $category ) : null; ?>
            <div class="col-md-4 mb-30 mb-md-60 mb-xl-90">
                <a href="<?php the_permalink(); ?>" class="excerpt animate-children">
                    <picture class="aspect-landscape bg-dark-grey mb-20">
                        <?php the_post_thumbnail( 'landscape-lg' ); ?>
                    </picture>
                    <div class="content">
                        <?php if ( $category ) : ?>
                        <span class="category eyebrow" data-href="<?php echo get_category_link( $category->term_id ) ?>" tabindex="0">
                            <?php echo $category->name; ?>
                        </span>
                        <?php endif; ?>
                        <h2 class="h3"><?php the_title(); ?></h2>
                        <?php DL::the_excerpt(); ?>
                    </div>
                </a>
            </div>
            <?php endwhile; else : ?>
            <div class="col-12 animate-children">
                <h2>Sorry, nothing was found.</h2>
            </div>
            <?php endif; ?>

            <?php if ( $next_posts_link ) : ?>
            <div class="col-12 text-center load-more animate">
                <?php echo str_replace( 'href', 'class="btn btn-outline-primary" href', $next_posts_link ); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>
