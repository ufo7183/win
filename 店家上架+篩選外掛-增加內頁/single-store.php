<?php
/**
 * The template for displaying a single store.
 */

get_header(); ?>

<div id="primary" class="content-area store-single-container">
    <main id="main" class="site-main" role="main">

        <?php
        // Start the loop.
        while ( have_posts() ) : the_post();

            // Get all custom fields
            $store_address = get_field('store_address');
            $store_phone = get_field('store_phone');
            $store_contact_person = get_field('store_contact_person');
            $store_business_hours = get_field('store_business_hours');
            $store_information = get_field('store_information');
            $google_maps_api_key = 'YOUR_GOOGLE_MAPS_API_KEY'; // <-- 請在這裡填入您的 Google Maps API 金鑰

            ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                <div class="top-section">
                    <div class="left-column">
                        <header class="entry-header">
                            <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                        </header><!-- .entry-header -->

                        <?php if ( $store_contact_person ) : ?>
                            <p><strong>聯絡人：</strong> <?php echo esc_html($store_contact_person); ?></p>
                        <?php endif; ?>

                        <?php if ( $store_phone ) : ?>
                            <p><strong>電話：</strong> <a href="tel:<?php echo esc_attr(preg_replace('/[^\d+]/', '', $store_phone)); ?>"><?php echo esc_html($store_phone); ?></a></p>
                        <?php endif; ?>

                        <?php if ( $store_address ) : ?>
                            <p><strong>地址：</strong> <?php echo esc_html($store_address); ?></p>
                        <?php endif; ?>

                        <?php if ( $store_business_hours ) : ?>
                            <div class="business-hours">
                                <strong>營業時間：</strong>
                                <?php echo wp_kses_post($store_business_hours); ?>
                            </div>
                        <?php endif; ?>

                    </div>
                    <div class="right-column">
                        <?php if ( $store_address ) : ?>
                            <div class="google-map-container">
                                <iframe
                                    src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3615.142248121051!2d121.517036!3d25.032969!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3442a85a80000001%3A0x7c2176d9e83f3f39!2z5L2g5aWz6aKY5YyX5YyX!5e0!3m2!1szh-TW!2stw!4v1694583999851!5m2!1szh-TW!2stw"
                                    width="100%"
                                    height="450"
                                    style="border:0;"
                                    allowfullscreen=""
                                    loading="lazy">
                                </iframe>
                            </div>
                        <?php else : ?>
                            <p style="color: red;">請在店家文章中填寫地址。</p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ( $store_information ) : ?>
                <div class="bottom-section">
                    <h2>店家資訊</h2>
                    <div class="entry-content">
                        <?php echo wp_kses_post($store_information); ?>
                    </div>
                </div>
                <?php endif; ?>

            </article><!-- #post-## -->

        <?php
        // End the loop.
        endwhile;
        ?>

    </main><!-- .site-main -->
</div><!-- .content-area -->

<?php get_footer();
