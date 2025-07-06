<?php
/*
Plugin Name: store Filter
Version: 1.4
Author: Your Name
*/

// Include ACF fields
require_once plugin_dir_path(__FILE__) . 'acf-fields.php';

if (!defined('ABSPATH')) exit;

// 1. 註冊「店家」Post Type
if (!function_exists('store_filter_register_post_type')) {
    function store_filter_register_post_type() {
        register_post_type('store', array(
            'labels' => array(
                'name'               => '店家',
                'singular_name'      => '店家',
                'menu_name'          => '店家管理',
                'add_new'            => '新增店家',
                'add_new_item'       => '新增店家',
                'edit_item'          => '編輯店家',
                'new_item'           => '新店家',
                'view_item'          => '查看店家',
                'search_items'       => '搜尋店家',
                'not_found'          => '找不到店家',
                'not_found_in_trash' => '回收桶中沒有店家'
            ),
            'public'      => true,
            'has_archive' => true,
            'rewrite'     => array('slug' => 'store'),
            'supports'    => array('title','editor','thumbnail'),
        ));
    }
    add_action('init', 'store_filter_register_post_type');
}

// 2. 註冊「店家地區」taxonomy
if (!function_exists('store_filter_register_taxonomy')) {
    function store_filter_register_taxonomy() {
        register_taxonomy('store_location', array('store'), array(
            'hierarchical' => true,
            'labels'       => array(
                'name'         => '地區',
                'singular_name'=> '地區',
                'menu_name'    => '地區',
                'all_items'    => '所有地區',
                'parent_item'  => '上層地區',
                'add_new_item' => '新增地區'
            ),
            'rewrite' => array('slug' => 'store-location')
        ));
    }
    add_action('init', 'store_filter_register_taxonomy');
}

// 3. 前端樣式 & JS (改為外部檔案)
if (!function_exists('store_filter_enqueue_scripts')) {
    function store_filter_enqueue_scripts() {
        wp_enqueue_script('jquery');

        // --- 載入外部 CSS ---
        wp_register_style(
            'store-filter-style',
            plugins_url('assets/css/store-filter.css', __FILE__),
            array(),
            '1.0'
        );
        wp_enqueue_style('store-filter-style');

        // --- 載入外部 JS ---
        wp_register_script(
            'store-filter-script',
            plugins_url('assets/js/store-filter.js', __FILE__),
            array('jquery'),
            '1.0',
            true
        );
        wp_enqueue_script('store-filter-script');

        // Localize for AJAX
        wp_localize_script('store-filter-script','storeAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('store_filter_nonce')
        ));
    }
    add_action('wp_enqueue_scripts', 'store_filter_enqueue_scripts', 999);
}

// 4. 短代碼：店家篩選器 (合併表單與列表)
if (!function_exists('store_filter_shortcode')) {
    function store_filter_shortcode() {
        $cities = get_terms(array(
            'taxonomy'   => 'store_location',
            'parent'     => 0,
            'hide_empty' => false
        ));
        ob_start();
        ?>
        <div class="store-filter-wrapper">
            <form id="store-filter-form">
                <select id="store_city" name="city">
                    <option value="">選擇縣市</option>
                    <?php foreach($cities as $city) : ?>
                        <option value="<?php echo esc_attr($city->term_id); ?>"><?php echo esc_html($city->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="store_area" name="area" disabled>
                    <option value="">選擇區域</option>
                </select>
                <input type="text" id="store_keyword" name="keyword" placeholder="搜尋店家名稱" />
                <button type="submit">搜尋</button>
            </form>
            <div id="store-list-results" class="store-list"></div>
            <div id="store-list-pagination"></div>
        </div>
        <?php
        return ob_get_clean();
    }
    add_shortcode('store_filter', 'store_filter_shortcode');
}

// 6. AJAX Handler for Filtering and Pagination
if (!function_exists('store_filter_ajax_handler')) {
    function store_filter_ajax_handler() {
        check_ajax_referer('store_filter_nonce', 'nonce');

        $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
        $city_id = isset($_POST['city']) ? intval($_POST['city']) : 0;
        $area_id = isset($_POST['area']) ? intval($_POST['area']) : 0;
        $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';

        $args = array(
            'post_type' => 'store',
            'posts_per_page' => 15,
            'paged' => $paged,
            's' => $keyword,
        );

        $tax_query = array('relation' => 'AND');
        if (!empty($area_id)) {
            $tax_query[] = array('taxonomy' => 'store_location', 'field' => 'term_id', 'terms' => $area_id);
        } elseif (!empty($city_id)) {
            $child_terms = get_terms(array('taxonomy' => 'store_location', 'parent' => $city_id, 'fields' => 'ids', 'hide_empty' => false));
            $terms_to_query = array_merge(array($city_id), $child_terms);
            $tax_query[] = array('taxonomy' => 'store_location', 'field' => 'term_id', 'terms' => $terms_to_query, 'operator' => 'IN');
        }

        if (count($tax_query) > 1) { // Only add tax_query if there's something to query
            $args['tax_query'] = $tax_query;
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Store Filter Query Args: ' . print_r($args, true));
        }

        $query = new WP_Query($args);
        $html = '';

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $store_image = get_field('store_image');
                $store_phone = get_field('store_phone');
                $store_address = get_field('store_address');
                $bg_image_style = $store_image ? 'style="background-image: url(' . esc_url($store_image['url']) . ');"' : '';

                $html .= '<div class="store-item" ' . $bg_image_style . '>';
                $html .= '  <div class="store-item-content">';
                $html .= '    <div class="store-item-name">' . get_the_title() . '</div>';
                if ($store_phone) {
                     $html .= '    <div class="store-item-phone">TEL：' . esc_html($store_phone) . '</div>';
                }
                if ($store_address) {
                    $html .= '    <div class="store-item-address">地址：' . esc_html($store_address) . '</div>';
                }
                $html .= '  </div>';
                $html .= '</div>';
            }
        } else {
            $html = '<p class="no-results">沒有找到符合條件的店家。</p>';
        }
        wp_reset_postdata();

        $pagination_html = paginate_links(array(
            'base' => '%_%',
            'format' => '?paged=%#%',
            'current' => max(1, $paged),
            'total' => $query->max_num_pages,
            'prev_next' => true,
            'prev_text' => '«',
            'next_text' => '»',
            'type' => 'plain',
        ));

        wp_send_json_success(array('html' => $html, 'pagination' => $pagination_html));
        wp_die();
    }
    add_action('wp_ajax_store_filter', 'store_filter_ajax_handler');
    add_action('wp_ajax_nopriv_store_filter', 'store_filter_ajax_handler');
}

// 7. AJAX：取得子區域
if (!function_exists('store_filter_get_districts_ajax')) {
    function store_filter_get_districts_ajax() {
        check_ajax_referer('store_filter_nonce', 'nonce'); // Changed from 'security'
        $city_id = isset($_POST['city_id']) ? intval($_POST['city_id']) : 0;
        $data = array();

        if ($city_id > 0) {
            $terms = get_terms(array(
                'taxonomy'   => 'store_location',
                'parent'     => $city_id,
                'hide_empty' => false
            ));
            if (!is_wp_error($terms) && !empty($terms)) {
                foreach ($terms as $t) {
                    $data[] = array('term_id' => $t->term_id, 'name' => $t->name);
                }
            }
        }
        wp_send_json_success($data);
        wp_die();
    }
    add_action('wp_ajax_store_filter_get_districts','store_filter_get_districts_ajax');
    add_action('wp_ajax_nopriv_store_filter_get_districts','store_filter_get_districts_ajax');
}

// 9. 後台：載入後台專用 JS & CSS
if (!function_exists('store_filter_admin_enqueue_scripts')) {
    function store_filter_admin_enqueue_scripts($hook) {
        global $post;

        // 只在編輯「店家」文章類型的頁面載入
        if ($hook == 'post.php' || $hook == 'post-new.php') {
            if (isset($post->post_type) && $post->post_type === 'store') {
                // 載入 Dashicons (收合圖示需要)
                wp_enqueue_style('dashicons');

                // 載入後台 CSS
                wp_enqueue_style(
                    'store-admin-style',
                    plugins_url('assets/css/store-admin.css', __FILE__),
                    array(),
                    '1.0'
                );

                // 載入後台 JS
                wp_enqueue_script(
                    'store-admin-script',
                    plugins_url('assets/js/store-admin.js', __FILE__),
                    array('jquery'),
                    '1.0',
                    true
                );
            }
        }
    }
    add_action('admin_enqueue_scripts', 'store_filter_admin_enqueue_scripts');
}
