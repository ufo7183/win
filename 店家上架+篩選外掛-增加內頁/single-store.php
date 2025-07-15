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
            $store_map_embed = get_field('store_map_embed');
            // 地圖嵌入代碼將直接從後台獲取

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
                        <?php if ( !empty($store_map_embed) ) : ?>
                            <div class="google-map-container" style="width: 100%; height: 450px;">
                                <?php 
                                // 直接輸出 iframe 代碼
                                echo $store_map_embed;
                                ?>
                            </div>
                        <?php else : ?>
                            <div style="background: #f5f5f5; padding: 20px; text-align: center; height: 200px; display: flex; align-items: center; justify-content: center;">
                                <p style="color: #666; margin: 0;">尚未設置地圖</p>
                            </div>
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
