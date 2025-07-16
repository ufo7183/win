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

                        <div class="social-media-links">
                            <?php if ( $facebook_url = get_field('facebook_url') ) : ?>
                                <a href="<?php echo esc_url($facebook_url); ?>" target="_blank" class="social-icon facebook" aria-label="Facebook">
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="40" height="40" viewBox="0 0 256 256" xml:space="preserve">
<g style="stroke: none; stroke-width: 0; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: none; fill-rule: nonzero; opacity: 1;" transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)">
	<path d="M 44.913 26.682 H 15.231 c -8.28 0 -15.002 -3.611 -15.211 -11.841 C 0.016 14.972 0 15.099 0 15.231 v 31.303 c 8.265 10.233 20.908 16.784 35.087 16.784 h 0.682 c 8.28 0 12.002 3.611 12.211 11.841 c 0.003 -0.131 0.02 -0.258 0.02 -0.39 C 48 83.181 44.181 90 35.769 90 h 39 C 83.181 90 90 83.181 90 74.769 C 90 42.732 69.814 26.682 44.913 26.682 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(55,82,139); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
	<path d="M 90 74.769 L 90 74.769 c 0 -24.901 -20.186 -45.087 -45.087 -45.087 H 15.231 c -8.28 0 -15.002 -6.611 -15.211 -14.841 C 0.016 14.972 0 15.099 0 15.231 C 0 6.819 6.819 0 15.231 0 h 59.538 C 83.181 0 90 6.819 90 15.231 V 74.769" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(60,90,153); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
	<path d="M 0 74.769 V 43.534 c 8.265 10.233 20.908 16.784 35.087 16.784 h 0.682 c 8.28 0 15.002 6.611 15.211 14.841 c 0.003 -0.131 0.02 -0.258 0.02 -0.39 C 51 83.181 44.181 90 35.769 90 H 15.231 C 6.819 90 0 83.181 0 74.769 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(49,73,125); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
	<path d="M 48.871 70 V 47.193 h 7.656 l 1.146 -8.889 h -8.802 v -5.675 c 0 -2.574 0.715 -4.327 4.405 -4.327 l 4.707 -0.002 v -7.95 C 57.169 20.241 54.376 20 51.125 20 c -6.786 0 -11.432 4.142 -11.432 11.749 v 6.555 h -7.676 v 8.889 h 7.675 V 70 L 48.871 70 L 48.871 70 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
</g>
</svg>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ( $instagram_url = get_field('instagram_url') ) : ?>
                                <a href="<?php echo esc_url($instagram_url); ?>" target="_blank" class="social-icon instagram" aria-label="Instagram">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 256 256" xml:space="preserve">
                                        <g style="stroke: none; stroke-width: 0; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: none; fill-rule: nonzero; opacity: 1;" transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)">
                                            <linearGradient id="SVGID_1" gradientUnits="userSpaceOnUse" x1="45" y1="90" x2="45" y2="-9.094947e-13">
                                                <stop offset="0%" style="stop-color:rgb(255,49,131);stop-opacity: 1"/>
                                                <stop offset="100%" style="stop-color:rgb(253,140,167);stop-opacity: 1"/>
                                            </linearGradient>
                                            <path d="M 90 63.718 C 90 78.233 78.233 90 63.718 90 H 26.282 C 11.767 90 0 78.233 0 63.718 V 26.282 C 0 11.767 11.767 0 26.282 0 h 37.436 C 78.233 0 90 11.767 90 26.282 V 63.718 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: url(#SVGID_1); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
                                            <path d="M 26.9 10.581 c -0.056 -1.235 -0.253 -2.079 -0.539 -2.817 c -0.291 -0.774 -0.748 -1.476 -1.338 -2.055 c -0.579 -0.59 -1.281 -1.047 -2.055 -1.339 c -0.738 -0.286 -1.581 -0.482 -2.817 -0.539 c -1.237 -0.056 -1.633 -0.07 -4.784 -0.07 s -3.547 0.014 -4.784 0.07 C 9.346 3.888 8.503 4.084 7.765 4.371 C 6.99 4.662 6.289 5.119 5.709 5.709 C 5.119 6.289 4.662 6.99 4.371 7.765 c -0.286 0.738 -0.482 1.582 -0.539 2.817 c -0.057 1.238 -0.07 1.633 -0.07 4.784 c 0 3.152 0.013 3.547 0.07 4.784 c 0.056 1.235 0.252 2.079 0.539 2.817 c 0.291 0.774 0.748 1.476 1.338 2.055 c 0.579 0.59 1.281 1.047 2.055 1.338 c 0.738 0.287 1.581 0.483 2.817 0.539 c 1.237 0.056 1.633 0.07 4.784 0.07 s 3.547 -0.013 4.784 -0.07 c 1.235 -0.056 2.079 -0.252 2.817 -0.539 c 1.559 -0.603 2.791 -1.835 3.393 -3.393 c 0.287 -0.738 0.483 -1.582 0.539 -2.817 c 0.056 -1.238 0.07 -1.633 0.07 -4.784 S 26.956 11.819 26.9 10.581 z M 24.811 20.055 c -0.052 1.132 -0.241 1.746 -0.399 2.155 c -0.39 1.012 -1.19 1.812 -2.202 2.202 c -0.409 0.159 -1.023 0.348 -2.155 0.399 c -1.223 0.056 -1.59 0.068 -4.689 0.068 c -3.099 0 -3.466 -0.012 -4.689 -0.068 c -1.131 -0.052 -1.746 -0.241 -2.155 -0.399 c -0.504 -0.186 -0.96 -0.483 -1.334 -0.868 c -0.386 -0.374 -0.682 -0.83 -0.868 -1.334 c -0.159 -0.409 -0.348 -1.023 -0.399 -2.155 c -0.056 -1.224 -0.068 -1.59 -0.068 -4.689 S 5.864 11.9 5.92 10.677 C 5.972 9.545 6.161 8.931 6.32 8.522 c 0.186 -0.504 0.483 -0.96 0.868 -1.334 c 0.374 -0.386 0.83 -0.682 1.334 -0.868 c 0.409 -0.159 1.023 -0.348 2.155 -0.4 c 1.223 -0.055 1.59 -0.067 4.689 -0.067 h 0 c 3.098 0 3.465 0.012 4.689 0.068 c 1.132 0.052 1.746 0.241 2.155 0.399 c 0.504 0.186 0.96 0.483 1.334 0.868 c 0.386 0.374 0.682 0.83 0.868 1.334 c 0.159 0.409 0.348 1.023 0.4 2.155 c 0.056 1.224 0.068 1.59 0.068 4.689 S 24.867 18.831 24.811 20.055 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1;" transform=" matrix(2.8008 0 0 2.8008 1.9639999999999986 1.9639999999999702) " stroke-linecap="round"/>
                                            <path d="M 15.365 9.407 c -3.291 0 -5.959 2.668 -5.959 5.959 s 2.668 5.959 5.959 5.959 c 3.291 0 5.959 -2.668 5.959 -5.959 S 18.656 9.407 15.365 9.407 z M 15.365 19.234 c -2.136 0 -3.868 -1.732 -3.868 -3.868 s 1.732 -3.868 3.868 -3.868 c 2.136 0 3.868 1.732 3.868 3.868 S 17.502 19.234 15.365 19.234 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1;" transform=" matrix(2.8008 0 0 2.8008 1.9639999999999986 1.9639999999999702) " stroke-linecap="round"/>
                                            <path d="M 22.952 9.171 c 0 0.769 -0.624 1.392 -1.392 1.392 c -0.769 0 -1.392 -0.624 -1.392 -1.392 c 0 -0.769 0.624 -1.392 1.392 -1.392 S 22.952 8.403 22.952 9.171 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1;" transform=" matrix(2.8008 0 0 2.8008 1.9639999999999986 1.9639999999999702) " stroke-linecap="round"/>
                                        </g>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ( $line_url = get_field('line_url') ) : ?>
                                <a href="<?php echo esc_url($line_url); ?>" target="_blank" class="social-icon line" aria-label="LINE">
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="40" height="40" viewBox="0 0 256 256" xml:space="preserve">
<g style="stroke: none; stroke-width: 0; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: none; fill-rule: nonzero; opacity: 1;" transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)">
	<path d="M 70.5 90 h -51 C 8.73 90 0 81.27 0 70.5 v -51 C 0 8.73 8.73 0 19.5 0 h 51 C 81.27 0 90 8.73 90 19.5 v 51 C 90 81.27 81.27 90 70.5 90 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(0,185,0); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
	<path d="M 77.622 41.042 c 0 -14.663 -14.699 -26.592 -32.768 -26.592 c -18.067 0 -32.768 11.929 -32.768 26.592 c 0 13.145 11.657 24.154 27.404 26.235 c 1.067 0.23 2.52 0.703 2.887 1.616 c 0.331 0.828 0.216 2.126 0.106 2.963 c 0 0 -0.384 2.312 -0.468 2.805 c -0.143 0.828 -0.658 3.24 2.838 1.766 c 3.498 -1.474 18.871 -11.112 25.746 -19.025 h -0.002 C 75.347 52.196 77.622 46.911 77.622 41.042" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
	<path d="M 38.196 33.957 h -2.299 c -0.352 0 -0.638 0.286 -0.638 0.637 v 14.278 c 0 0.352 0.286 0.637 0.638 0.637 h 2.299 c 0.352 0 0.638 -0.285 0.638 -0.637 V 34.594 C 38.834 34.243 38.548 33.957 38.196 33.957" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(0,185,0); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
	<path d="M 54.016 33.957 h -2.298 c -0.353 0 -0.638 0.286 -0.638 0.637 v 8.483 l -6.543 -8.836 c -0.015 -0.023 -0.032 -0.044 -0.05 -0.065 c -0.002 -0.002 -0.003 -0.003 -0.004 -0.004 c -0.012 -0.014 -0.025 -0.027 -0.038 -0.039 c -0.004 -0.004 -0.008 -0.007 -0.012 -0.011 c -0.011 -0.01 -0.022 -0.02 -0.034 -0.028 c -0.005 -0.005 -0.011 -0.009 -0.017 -0.013 c -0.011 -0.008 -0.022 -0.016 -0.033 -0.023 c -0.006 -0.004 -0.012 -0.008 -0.019 -0.011 c -0.011 -0.007 -0.023 -0.013 -0.034 -0.019 c -0.007 -0.003 -0.013 -0.007 -0.02 -0.01 c -0.012 -0.005 -0.024 -0.011 -0.037 -0.016 c -0.007 -0.003 -0.013 -0.005 -0.021 -0.007 c -0.012 -0.005 -0.025 -0.009 -0.038 -0.012 c -0.007 -0.002 -0.014 -0.004 -0.022 -0.006 c -0.012 -0.003 -0.024 -0.006 -0.037 -0.008 c -0.009 -0.002 -0.018 -0.003 -0.027 -0.004 c -0.011 -0.002 -0.022 -0.003 -0.034 -0.004 c -0.011 -0.001 -0.022 -0.002 -0.033 -0.002 c -0.008 0 -0.014 -0.001 -0.022 -0.001 h -2.298 c -0.352 0 -0.638 0.286 -0.638 0.637 v 14.278 c 0 0.352 0.286 0.637 0.638 0.637 h 2.298 c 0.353 0 0.639 -0.285 0.639 -0.637 v -8.48 l 6.551 8.848 c 0.045 0.064 0.101 0.116 0.162 0.157 c 0.002 0.002 0.005 0.003 0.007 0.005 c 0.013 0.008 0.026 0.017 0.039 0.024 c 0.006 0.004 0.012 0.007 0.018 0.01 c 0.01 0.005 0.02 0.01 0.031 0.015 c 0.01 0.004 0.02 0.009 0.031 0.013 c 0.007 0.003 0.012 0.005 0.019 0.007 c 0.015 0.005 0.029 0.01 0.043 0.014 c 0.003 0.001 0.006 0.002 0.009 0.002 c 0.052 0.014 0.107 0.022 0.163 0.022 h 2.298 c 0.353 0 0.638 -0.285 0.638 -0.637 V 34.594 C 54.655 34.243 54.369 33.957 54.016 33.957" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(0,185,0); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
	<path d="M 32.655 45.934 H 26.41 V 34.595 c 0 -0.352 -0.286 -0.638 -0.638 -0.638 h -2.299 c -0.352 0 -0.638 0.286 -0.638 0.638 v 14.276 v 0.001 c 0 0.171 0.068 0.326 0.178 0.441 c 0.003 0.003 0.005 0.007 0.009 0.01 c 0.003 0.003 0.006 0.006 0.009 0.009 c 0.115 0.11 0.269 0.178 0.441 0.178 h 0.001 h 9.182 c 0.352 0 0.637 -0.286 0.637 -0.638 v -2.299 C 33.293 46.22 33.008 45.934 32.655 45.934" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(0,185,0); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
	<path d="M 66.709 37.532 c 0.352 0 0.637 -0.286 0.637 -0.638 v -2.298 c 0 -0.353 -0.285 -0.639 -0.637 -0.639 h -9.182 h -0.001 c -0.172 0 -0.327 0.069 -0.443 0.18 c -0.002 0.003 -0.005 0.005 -0.007 0.007 c -0.004 0.004 -0.007 0.008 -0.01 0.011 c -0.109 0.114 -0.177 0.269 -0.177 0.44 v 0.001 v 14.275 v 0.001 c 0 0.171 0.068 0.326 0.178 0.441 c 0.003 0.003 0.006 0.007 0.009 0.01 c 0.003 0.003 0.006 0.006 0.009 0.008 c 0.114 0.11 0.269 0.178 0.441 0.178 h 0.001 h 9.182 c 0.352 0 0.637 -0.286 0.637 -0.638 v -2.299 c 0 -0.352 -0.285 -0.638 -0.637 -0.638 h -6.245 V 43.52 h 6.245 c 0.352 0 0.637 -0.286 0.637 -0.638 v -2.298 c 0 -0.353 -0.285 -0.639 -0.637 -0.639 h -6.245 v -2.413 H 66.709 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(0,185,0); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round"/>
</g>
</svg>
                                </a>
                            <?php endif; ?>
                        </div>

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
