/**
 * RS Video Slider Shortcode
 * 使用方式: [rs_videoslider]
 */

// 防止直接訪問
if (!defined('ABSPATH')) {
    exit;
}

// 註冊 ACF 重複器欄位
add_action('acf/init', 'rs_register_video_slider_fields');
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
                        'value' => 'teams',
                    ),
                ),
            ),
        ));
    }
}

// 註冊 Shortcode
add_shortcode('rs_videoslider', 'rs_video_slider_shortcode');
function rs_video_slider_shortcode($atts) {
    // 設定預設值
    $atts = shortcode_atts(array(
        'speed' => 5000, // 輪播速度 (毫秒)
    ), $atts);
    
    // 獲取當前文章 ID
    $post_id = get_the_ID();
    
    // 獲取重複器資料
    $videos = get_field('rs_video_repeater', $post_id);
    
    // 如果沒有影片，返回空
    if (empty($videos)) {
        return '';
    }
    
    // 複製影片陣列以創建無縫循環
    $total_videos = count($videos);
    $extended_videos = array_merge($videos, $videos, $videos, $videos, $videos);
    
    // 開始輸出
    ob_start();
    ?>
    
    <div class="rs-video-slider-container">
        <div class="rs-video-slider-wrapper">
            <!-- 左箭頭 -->
            <button class="rs-slider-arrow rs-prev-arrow" aria-label="Previous">
                <img src="https://wordpress-1391714-5531969.cloudwaysapps.com/wp-content/uploads/2025/06/Frame-1000002219-2.svg" alt="Previous">
            </button>
            
            <!-- 輪播區域 -->
            <div class="rs-video-slider" data-speed="<?php echo esc_attr($atts['speed']); ?>" data-total="<?php echo $total_videos; ?>">
                <div class="rs-video-viewport">
                    <div class="rs-video-track">
                        <?php foreach ($extended_videos as $index => $video) : 
                            $real_index = $index % $total_videos;
                        ?>
                            <div class="rs-video-slide" data-index="<?php echo $real_index; ?>">
                                <div class="rs-video-item">
                                    <div class="rs-video-wrapper">
                                        <?php echo rs_generate_video_embed($video['rs_video_url']); ?>
                                        <div class="rs-video-overlay"></div>
                                    </div>
                                    <h3 class="rs-video-title"><?php echo esc_html($video['rs_video_title']); ?></h3>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- 右箭頭 -->
            <button class="rs-slider-arrow rs-next-arrow" aria-label="Next">
                <img src="https://wordpress-1391714-5531969.cloudwaysapps.com/wp-content/uploads/2025/06/Frame-1000002218-1.svg" alt="Next">
            </button>
        </div>
    </div>
    
    <style>
    .rs-video-slider-container {
        width: 100%;
        margin: 0 auto;
        box-sizing: border-box;
        /* 固定高度容器，防止跳動 */
        height: 400px; /* 設定固定高度，確保容器尺寸穩定 */
        overflow: hidden; /* 確保內容不超出容器 */
        position: relative;
    }
    
    .rs-video-slider-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
        width: 100%;
        height: 100%; /* 確保wrapper填滿容器 */
    }
    
    .rs-video-slider {
        position: relative;
        width: 100%;
        max-width: 1033px; /* 230 + 35 + 468 + 35 + 230 + 35 */
        height: 100%; /* 確保slider填滿wrapper */
    }
    
    .rs-video-viewport {
        overflow: hidden;
        position: relative;
        width: 100%;
        margin: 0 auto;
        max-width: 963px; /* 顯示區域 */
        height: 100%; /* 確保viewport填滿slider */
    }
    
    .rs-video-track {
        display: flex;
        gap: 35px;
        transition: transform 0.5s ease;
        align-items: center; /* 垂直置中對齊 */
        height: 100%; /* 確保track填滿viewport */
        will-change: transform; /* 優化動畫性能 */
    }
    
    .rs-video-slide {
        width: 230px;
    flex-shrink: 0;
    transition: all 0.5s ease;
    
    transform: scale(0.9);
        
        display: flex;
        align-items: center; /* 垂直置中 */
        backface-visibility: hidden; /* 防止閃爍 */
        transform: translateZ(0); /* 硬體加速 */
        height: 100%; /* 確保slide填滿track */
    }
    
    .rs-video-slide.rs-center {
        width: 468px;
    opacity: 1;
    transform: scale(1);
    z-index: 1;
    }
    
    .rs-video-item {
        width: 100%;
        height: 100%; /* 確保item填滿slide */
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .rs-video-wrapper {
        position: relative;
        width: 100%;
        overflow: hidden;
        background: #000;
        transition: all 0.5s ease;
    }
    
    .rs-video-slide:not(.rs-center) .rs-video-wrapper {
        height: 129px;
    }
    
    .rs-video-slide.rs-center .rs-video-wrapper {
        height: 263px;
    }
    
    .rs-video-wrapper iframe,
    .rs-video-wrapper video {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    /* 遮罩 */
    .rs-video-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.20);
        pointer-events: none;
        opacity: 1;
        transition: opacity 0.3s ease;
    }
    
    .rs-video-slide.rs-center .rs-video-overlay {
        opacity: 0;
    }
    
    /* 標題樣式 */
    .rs-video-title {
        color: var(--Color, #A2B6F2);
        text-align: center;
        font-family: "Noto Serif TC";
        font-size: 15px;
        font-style: normal;
        font-weight: 500;
        line-height: 30px;
        letter-spacing: 2.25px;
        margin-top: 18px;
        margin-bottom: 0;
    }
    
    /* 箭頭樣式 */
    .rs-slider-arrow {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .rs-slider-arrow img {
        width: 64px;
        height: 64px;
        display: block;
    }
    
    /* 響應式設計 - 平板和手機 */
    @media (max-width: 768px) {
        .rs-video-slider-container {
            padding: 0; /* 移除左右空隙 */
            height: 350px; /* 平板高度：468px * 9/16 = 263px + 標題空間 */
        }
        
        .rs-video-slider-wrapper {
            gap: 0; /* 移除箭頭和輪播之間的間距 */
            justify-content: space-between; /* 平均分布 */
        }
        
        .rs-slider-arrow img {
            width: 40px; /* 調整箭頭大小 */
            height: 40px;
        }
        
        .rs-video-slider {
            max-width: none;
            flex: 1; /* 讓輪播區域佔據剩餘空間 */
        }
        
        .rs-video-viewport {
            max-width: none; /* 移除最大寬度限制 */
            width: 100%;
            overflow: hidden; /* 確保只顯示一個視頻 */
        }
        
        .rs-video-slide,
        .rs-video-slide.rs-center {
            flex: 0 0 468px; /* 平板固定寬度468px */
            min-width: 468px; /* 確保最小寬度 */
        }
        
        .rs-video-slide .rs-video-wrapper,
        .rs-video-slide.rs-center .rs-video-wrapper {
            height: 263px; /* 468px * 9/16 = 263px */
            padding-bottom: 0; /* 移除padding-bottom */
        }
        
        .rs-video-overlay {
            opacity: 0;
        }
        
        .rs-video-track,
        .swiper-wrapper {
            gap: 0; /* 移除間距 */
            justify-content: flex-start; /* 左對齊，避免居中顯示兩個 */
        }
        
        /* 調整標題大小 */
        .rs-video-title {
            font-size: 16px;
            line-height: 24px;
            letter-spacing: 1.5px;
            margin-top: 15px;
        }
    }
    
    /* 手機專用樣式 */
    @media (max-width: 480px) {
        .rs-video-slider-container {
            padding: 0; /* 移除左右空隙 */
            height: 200px; /* 手機高度：230px * 9/16 = 129px + 標題空間 */
        }
        
        .rs-slider-arrow img {
            width: 35px; /* 手機更小的箭頭 */
            height: 35px;
        }
        
        .rs-video-slide,
        .rs-video-slide.rs-center {
            width: 230px;
    flex-shrink: 0;
        }
        
        .rs-video-slide .rs-video-wrapper,
        .rs-video-slide.rs-center .rs-video-wrapper {
            height: 129px; /* 230px * 9/16 = 129px */
            padding-bottom: 0; /* 移除padding-bottom */
        }
        
        .rs-video-track {
            gap: 0!important; /* 移除間距 */
            justify-content: flex-start; /* 左對齊，避免居中顯示兩個 */
        }
        
        /* 手機標題更小 */
        .rs-video-title {
            font-size: 12px;
            line-height: 18px;
            letter-spacing: 1px;
            margin-top: 8px;
        }
    }
    </style>
    
    <script>
document.addEventListener("DOMContentLoaded", function () {
  const sliders = document.querySelectorAll(".rs-video-slider");

  sliders.forEach(function (sliderEl) {
    const swiperContainer = sliderEl.querySelector(".rs-video-track");
    const slides = swiperContainer.querySelectorAll(".rs-video-slide");

    swiperContainer.classList.add("swiper-wrapper");
    slides.forEach((slide) => slide.classList.add("swiper-slide"));

    const swiper = new Swiper(sliderEl.querySelector(".rs-video-viewport"), {
      loop: true,
      centeredSlides: true,
      slidesPerView: "auto",
      spaceBetween: 35,
      autoplay: {
        delay: parseInt(sliderEl.dataset.speed) || 5000,
        disableOnInteraction: false,
      },
      navigation: {
        nextEl: sliderEl.closest(".rs-video-slider-wrapper").querySelector(".rs-next-arrow"),
        prevEl: sliderEl.closest(".rs-video-slider-wrapper").querySelector(".rs-prev-arrow"),
      },
      breakpoints: {
        0: {
          slidesPerView: 1,
          centeredSlides: true,
          spaceBetween: 0,
        },
        481: {
          slidesPerView: "auto",
          centeredSlides: true,
          spaceBetween: 35,
        },
      },
      on: {
        slideChangeTransitionEnd: function () {
          updateSlideClass(this);
        },
        afterInit: function () {
          updateSlideClass(this);
        },
      },
    });

    function updateSlideClass(swiper) {
      swiper.slides.forEach((slide) => {
        slide.classList.remove("rs-center");
      });
      const activeSlide = swiper.slides[swiper.activeIndex];
      if (activeSlide) {
        activeSlide.classList.add("rs-center");
      }
    }
  });
});
</script>

    
    <?php
    return ob_get_clean();
}


// 生成影片嵌入代碼
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