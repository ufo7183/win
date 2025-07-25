<?php
/*
Plugin Name: Clinic Filter
Version: 1.0.1
Author: Your Name
*/

if (!defined('ABSPATH')) exit;

// 1. 註冊「診所」Post Type
if (!function_exists('clinic_filter_register_post_type')) {
    function clinic_filter_register_post_type() {
        register_post_type('clinic', array(
            'labels' => array(
                'name'               => '診所',
                'singular_name'      => '診所',
                'menu_name'          => '診所管理',
                'add_new'            => '新增診所',
                'add_new_item'       => '新增診所',
                'edit_item'          => '編輯診所',
                'new_item'           => '新診所',
                'view_item'          => '查看診所',
                'search_items'       => '搜尋診所',
                'not_found'          => '找不到診所',
                'not_found_in_trash' => '回收桶中沒有診所'
            ),
            'public'      => true,
            'has_archive' => true,
            'rewrite'     => array('slug' => 'clinic'),
            'supports'    => array('title','editor','thumbnail'),
        ));
    }
    add_action('init', 'clinic_filter_register_post_type');
}

// 2. 註冊「診所地區」taxonomy
function clinic_filter_register_taxonomy_init() {
    $labels = array(
        'name'              => '縣市',
        'singular_name'     => '縣市',
        'search_items'      => '搜尋縣市',
        'all_items'         => '所有縣市',
        'parent_item'       => '上層縣市',
        'parent_item_colon' => '上層縣市:',
        'edit_item'         => '編輯縣市',
        'update_item'       => '更新縣市',
        'add_new_item'      => '新增縣市',
        'new_item_name'     => '新縣市名稱',
        'menu_name'         => '縣市',
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'clinic-location'),
    );

    register_taxonomy('clinic_location', array('clinic'), $args);
}
add_action('init', 'clinic_filter_register_taxonomy_init', 0);

// 3. 前端樣式 & JS
if (!function_exists('clinic_filter_enqueue_scripts')) {
    function clinic_filter_enqueue_scripts() {
        // 載入 jQuery
        wp_enqueue_script('jquery');

        // 前端樣式
        wp_enqueue_style(
            'clinic-filter-style',
            plugins_url('assets/css/clinic-filter.css', __FILE__),
            array(),
            '1.0.0'
        );

        // 前端腳本
        wp_enqueue_script(
            'clinic-filter-script',
            plugins_url('assets/js/clinic-filter.js', __FILE__),
            array('jquery'),
            '1.0.0',
            true
        );

        // 本地化腳本
        wp_localize_script('clinic-filter-script', 'clinicAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('clinic_filter_nonce')
        ));
    }
    add_action('wp_enqueue_scripts', 'clinic_filter_enqueue_scripts');
}

// 4. 短代碼：搜尋表單
if (!function_exists('clinic_search_bar_shortcode')) {
    function clinic_search_bar_shortcode($atts) {
        $atts = shortcode_atts(array(), $atts, 'clinic_search_bar');
        
        ob_start();
        ?>
        <div class="clinic-filter-container">
            <form id="clinic-filter-form" class="row g-3 align-items-center">
                <div class="col-12 col-md-3">
                    <button type="button" id="reset-filters" class="btn btn-primary w-100">
                        <i class="fas fa-undo"></i> 全部診所
                    </button>
                </div>
                <div class="col-12 col-md-3">
                    <input type="text" class="form-control" id="clinic-keyword" placeholder="搜尋診所...">
                </div>
                <div class="col-12 col-md-3">
                    <?php
                    $cities = get_terms(array(
                        'taxonomy'   => 'clinic_location',
                        'hide_empty' => false,
                        'parent'     => 0
                    ));
                    ?>
                    <select class="form-select" id="clinic-city">
                        <option value="">選擇地區</option>
                        <?php foreach ($cities as $city) : ?>
                            <option value="<?php echo esc_attr($city->term_id); ?>">
                                <?php echo esc_html($city->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <select class="form-select" id="clinic-district" disabled>
                        <option value="">選擇縣市</option>
                    </select>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    add_shortcode('clinic_search_bar', 'clinic_search_bar_shortcode');
}

// 5. 短代碼：診所列表
if (!function_exists('clinic_list_shortcode')) {
    function clinic_list_shortcode($atts) {
        $atts = shortcode_atts(array(
            'posts_per_page' => 10,
        ), $atts, 'clinic_list');
        
        ob_start();
        ?>
        <div id="clinic-list-container">
            <?php echo clinic_filter_generate_list(); ?>
        </div>
        <div id="clinic-loading" class="text-center my-3" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">載入中...</span>
            </div>
        </div>
        <div id="no-more-results" class="text-center my-3" style="display: none;">
            <p>沒有更多結果</p>
        </div>
        <?php
        return ob_get_clean();
    }
    add_shortcode('clinic_list', 'clinic_list_shortcode');
}

// 6. 產生列表 HTML
if (!function_exists('clinic_filter_generate_list')) {
    function clinic_filter_generate_list($city_id = 0, $area_id = 0, $keyword = '', $offset = 0) {
        $args = array(
            'post_type'      => 'clinic',
            'posts_per_page' => 25, // 每次載入 25 筆
            'offset'         => $offset,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        );

        $tax_query = array();
        
        if ($city_id > 0) {
            $tax_query[] = array(
                'taxonomy' => 'clinic_location',
                'field'    => 'term_id',
                'terms'    => $city_id,
            );
        }
        
        if ($area_id > 0) {
            $tax_query[] = array(
                'taxonomy' => 'clinic_location',
                'field'    => 'term_id',
                'terms'    => $area_id,
            );
        }
        
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }
        
        if (!empty($keyword)) {
            $args['s'] = $keyword;
        }

        $query = new WP_Query($args);
        
        // 計算總數
        $total_query = new WP_Query(array_merge($args, array('posts_per_page' => -1)));
        $total = $total_query->post_count;
        
        ob_start();
        
        if ($query->have_posts()) {
            echo '<div class="row">';
            while ($query->have_posts()) {
                $query->the_post();
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <?php if (has_post_thumbnail()) : ?>
                            <img src="<?php the_post_thumbnail_url('medium'); ?>" class="card-img-top" alt="<?php the_title(); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php the_title(); ?></h5>
                            <div class="card-text">
                                <?php 
                                $location = get_the_terms(get_the_ID(), 'clinic_location');
                                if ($location && !is_wp_error($location)) {
                                    echo '<p><i class="fas fa-map-marker-alt"></i> ';
                                    $location_names = array();
                                    foreach ($location as $loc) {
                                        $location_names[] = $loc->name;
                                    }
                                    echo esc_html(implode(', ', $location_names));
                                    echo '</p>';
                                }
                                ?>
                                <?php the_excerpt(); ?>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="<?php the_permalink(); ?>" class="btn btn-primary">查看詳情</a>
                        </div>
                    </div>
                </div>
                <?php
            }
            echo '</div>';
            
            // 載入更多按鈕
            if (($offset + $query->post_count) < $total) {
                echo '<div class="text-center mt-4">';
                echo '<button id="load-more" class="btn btn-outline-primary" 
                       data-city="' . esc_attr($city_id) . '" 
                       data-area="' . esc_attr($area_id) . '" 
                       data-keyword="' . esc_attr($keyword) . '" 
                       data-offset="' . ($offset + $query->post_count) . '">
                        載入更多
                    </button>';
                echo '</div>';
            }
            
            wp_reset_postdata();
            
            if ($query->have_posts()) {
                return array(
                    'success' => true,
                    'data' => ob_get_clean(),
                    'has_more' => ($total > ($offset + 25)) // 更新為 25 筆判斷
                );
            } else {
                $message = !empty($keyword) ? '搜尋不到此診所' : '此地區暫無認證診所';
                return array(
                    'success' => false,
                    'data' => '<div class="alert alert-info">' . $message . '</div>',
                    'has_more' => false
                );
            }
        }
    }
}

// 7. AJAX：取得子區域
if (!function_exists('clinic_filter_get_districts_ajax')) {
    function clinic_filter_get_districts_ajax() {
        check_ajax_referer('clinic_filter_nonce', 'nonce');
        
        $city_id = isset($_POST['city_id']) ? intval($_POST['city_id']) : 0;
        
        if ($city_id <= 0) {
            wp_send_json_error('無效的城市 ID');
        }
        
        $districts = get_terms(array(
            'taxonomy'   => 'clinic_location',
            'hide_empty' => false,
            'parent'     => $city_id
        ));
        
        if (is_wp_error($districts)) {
            wp_send_json_error($districts->get_error_message());
        }
        
        $options = '<option value="">選擇縣市</option>';
        foreach ($districts as $district) {
            $options .= sprintf(
                '<option value="%s">%s</option>',
                esc_attr($district->term_id),
                esc_html($district->name)
            );
        }
        
        wp_send_json_success($options);
    }
    add_action('wp_ajax_clinic_filter_get_districts', 'clinic_filter_get_districts_ajax');
    add_action('wp_ajax_nopriv_clinic_filter_get_districts', 'clinic_filter_get_districts_ajax');
}

// 8. AJAX：篩選
if (!function_exists('clinic_filter_ajax_search')) {
    function clinic_filter_ajax_search() {
        check_ajax_referer('clinic_filter_nonce', 'nonce');
        
        $city_id  = isset($_POST['city_id']) ? intval($_POST['city_id']) : 0;
        $area_id  = isset($_POST['area_id']) ? intval($_POST['area_id']) : 0;
        $keyword  = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
        $offset   = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        
        $result = clinic_filter_generate_list($city_id, $area_id, $keyword, $offset);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error('沒有找到符合條件的診所');
        }
    }
    add_action('wp_ajax_clinic_filter', 'clinic_filter_ajax_search');
    add_action('wp_ajax_nopriv_clinic_filter', 'clinic_filter_ajax_search');
}

// 9. 後台：載入後台專用 JS & CSS
if (!function_exists('clinic_filter_admin_enqueue_scripts')) {
    function clinic_filter_admin_enqueue_scripts($hook) {
        // 只在編輯頁面載入
        if ($hook == 'post.php' || $hook == 'post-new.php') {
            global $post;
            if ($post && $post->post_type === 'clinic') {
                // 載入 Dashicons (收合圖示需要)
                wp_enqueue_style('dashicons');

                // 載入後台 CSS
                wp_enqueue_style(
                    'clinic-admin-style',
                    plugins_url('assets/css/clinic-admin.css', __FILE__),
                    array(),
                    '1.0.0'
                );

                // 載入後台 JS
                wp_enqueue_script(
                    'clinic-admin-script',
                    plugins_url('assets/js/clinic-admin.js', __FILE__),
                    array('jquery'),
                    '1.0.0',
                    true
                );
            }
        }
    }
    add_action('admin_enqueue_scripts', 'clinic_filter_admin_enqueue_scripts');
}
