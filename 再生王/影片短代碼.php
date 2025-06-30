/**
 * RS Video Slider Shortcode
 * 使用方式: [rs_videoslider]
 */

// 防止直接訪問
if (!defined('ABSPATH')) {
    exit;
}

// 註冊 ACF 重複器欄位
if (!function_exists('rs_register_video_slider_fields')) {
    function rs_register_video_slider_fields() {
        if (function_exists('acf_add_local_field_group')) {
            acf_add_local_field_group(array(
                'key' => 'group_rs_video_slider',
                'title' => 'RS 影片輪播設定',
                'fields' => array(
                    array(
                        'key' => 'field_rs_video_repeater',
                        'label' => '影片列表',
                        'name' => 'rs_video_repeater',
                        'type' => 'repeater',
                        'layout' => 'table',
                        'button_label' => '新增影片',
                        'sub_fields' => array(
                            array(
                                'key' => 'field_rs_video_title',
                                'label' => '影片標題',
                                'name' => 'rs_video_title',
                                'type' => 'text',
                                'required' => 1,
                            ),
                            array(
                                'key' => 'field_rs_video_url',
                                'label' => '影片網址',
                                'name' => 'rs_video_url',
                                'type' => 'url',
                                'required' => 1,
                            ),
                        ),
                    ),
                ),
                'location' => array(
                    array(
                        array(
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'post',
                        ),
                    ),
                ),
            ));
        }
    }
    add_action('acf/init', 'rs_register_video_slider_fields');
}

// 註冊 Shortcode
if (!function_exists('rs_video_slider_shortcode')) {
    function rs_video_slider_shortcode($atts) {
        // 設定預設值
        $atts = shortcode_atts(array(
            'speed' => 5000, // 輪播速度 (毫秒)
        ), $atts);

        // 獲取當前文章 ID
        $post_id = get_the_ID();
        $videos = get_field('rs_video_repeater', $post_id);

        if (empty($videos)) {
            return '';
        }

        $slider_id = 'rs-video-slider-' . uniqid();

        ob_start();
        ?>
        <div id="<?php echo $slider_id; ?>" class="rs-video-slider-container">
            <div class="rs-video-slider-navigation">
                 <button class="rs-slider-arrow rs-prev-arrow" aria-label="Previous">
                    <img src="https://wordpress-1391714-5531969.cloudwaysapps.com/wp-content/uploads/2025/06/Frame-1000002219-2.svg" alt="Previous">
                </button>
            </div>

            <div class="owl-carousel rs-video-slider">
                <?php foreach ($videos as $video) : ?>
                    <div class="rs-item">
                        <div class="rs-video-item">
                            <div class="rs-video-wrapper">
                                <?php echo rs_generate_video_embed($video['rs_video_url']); ?>
                            </div>
                            <h3 class="rs-video-title"><?php echo esc_html($video['rs_video_title']); ?></h3>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="rs-video-slider-navigation">
                <button class="rs-slider-arrow rs-next-arrow" aria-label="Next">
                    <img src="https://wordpress-1391714-5531969.cloudwaysapps.com/wp-content/uploads/2025/06/Frame-1000002218-1.svg" alt="Next">
                </button>
            </div>
        </div>

        <style>
        .rs-video-slider-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            gap: 20px; /* 1. Arrow distance */
            box-sizing: border-box;
            padding: 0 10px;
        }
        .rs-video-slider-navigation { /* 4. Ensure arrows are visible */
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }
        .rs-video-slider {
            flex: 1; /* 4. Allow slider to fill space */
            min-width: 0; /* 4. Allow slider to shrink */
            max-width: 990px;
        }
        .rs-video-slider .rs-item {
            text-align: center;
            padding: 30px 0px;
        }
        .rs-video-slider .rs-video-item {
            transition: transform 0.4s ease, opacity 0.4s ease;
            opacity: 0.7;
            transform: scale(0.65);
        }
        .rs-video-slider .owl-item.center .rs-video-item {
            opacity: 1;
            transform: scale(1.2);
            z-index: 2;
        }
        .rs-video-wrapper {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 */
            height: 0;
            background: #000;
            overflow: hidden;
            border-radius: 15px; /* 2. Video corner radius */
        }
        .rs-video-wrapper iframe, .rs-video-wrapper video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        .rs-video-title {
            color: #A2B6F2;
            text-align: center;
            font-family: "Noto Serif TC";
            font-size: 15px; /* 5. Title size */
            font-weight: 500;
            line-height: 30px;
            margin-top: 18px;
        }
        .rs-slider-arrow {
            background: none; 
            border: none; 
            cursor: pointer; 
            padding: 0; 
            z-index: 10;
            box-shadow: none; /* 3. Remove arrow shadow */
        }
        .rs-slider-arrow:hover,
        .rs-slider-arrow:active,
        .rs-slider-arrow:focus {
            background: transparent !important;
            box-shadow: none !important;
            outline: none !important;
        }
        @media (max-width: 767px) {
            .rs-video-slider-container { 
                gap: 10px; /* 4. Adjust gap for mobile */
            }
            .rs-video-slider .owl-item.center .rs-video-item {
                transform: scale(1.05);
            }
            .rs-slider-arrow img { width: 35px; height: 35px; }
        }
        @media (max-width: 599px) {
            .rs-video-slider .rs-video-item {
                transform: scale(0.85);
                opacity: 1;
            }
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            var sliderContainer = $('#<?php echo $slider_id; ?>');
            var owl = sliderContainer.find('.rs-video-slider');

            owl.owlCarousel({
                loop: true,
                center: true,
                margin: 10,
                responsiveClass: true,
                responsive: {
                    0: { items: 1, center: false },
                    600: { items: 3, center: true },
                    1000: { items: 3, center: true }
                }
            });

            // Custom Navigation Events
            sliderContainer.find('.rs-next-arrow').click(function() {
                owl.trigger('next.owl.carousel');
            });
            sliderContainer.find('.rs-prev-arrow').click(function() {
                owl.trigger('prev.owl.carousel');
            });

            // Pause videos on slide change
            owl.on('change.owl.carousel', function(event) {
                sliderContainer.find('iframe').each(function(){
                    $(this)[0].contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
                });
                sliderContainer.find('video').each(function(){
                    this.pause();
                });
            });
        });
        </script>

        <?php
        return ob_get_clean();
    }
    add_shortcode('rs_videoslider', 'rs_video_slider_shortcode');
}

// 生成影片嵌入代碼
if (!function_exists('rs_generate_video_embed')) {
    function rs_generate_video_embed($url) {
    $embed_code = '';
    
    // YouTube
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches)) {
        $video_id = $matches[1];
        $embed_code = '<iframe src="https://www.youtube.com/embed/' . $video_id . '?rel=0&showinfo=0" frameborder="0" allowfullscreen></iframe>';
    }
    // Vimeo
    elseif (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
        $video_id = $matches[1];
        $embed_code = '<iframe src="https://player.vimeo.com/video/' . $video_id . '?title=0&byline=0&portrait=0" frameborder="0" allowfullscreen></iframe>';
    }
    // 本機影片
    else {
        $embed_code = '<video controls><source src="' . esc_url($url) . '" type="video/mp4">Your browser does not support the video tag.</video>';
    }
    
    return $embed_code;
    }
}