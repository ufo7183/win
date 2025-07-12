<?php
/**
 * The template for displaying a single store.
 */

get_header(); ?>

<div id="primary" class="content-area" style="padding: 2em;">
    <main id="main" class="site-main" role="main">

        <?php
        // Start the loop.
        while ( have_posts() ) : the_post();

            // Get custom fields
            $store_phone = get_field('store_phone');
            $store_address = get_field('store_address');
            ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                </header><!-- .entry-header -->

                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="post-thumbnail">
                        <?php the_post_thumbnail('large'); ?>
                    </div><!-- .post-thumbnail -->
                <?php endif; ?>

                <div class="entry-content">
                    <?php
                    if ( $store_phone ) {
                        echo '<p><strong>電話：</strong> <a href="tel:' . esc_attr(preg_replace('/[^\d+]/', '', $store_phone)) . '">' . esc_html($store_phone) . '</a></p>';
                    }
                    if ( $store_address ) {
                        echo '<p><strong>地址：</strong> ' . esc_html($store_address) . '</p>';
                    }
                    
                    // Display main content if any
                    the_content();
                    ?>
                </div><!-- .entry-content -->

            </article><!-- #post-## -->

        <?php
        // End the loop.
        endwhile;
        ?>

    </main><!-- .site-main -->
</div><!-- .content-area -->

<?php get_footer();
