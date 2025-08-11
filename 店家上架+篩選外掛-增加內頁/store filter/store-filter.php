<?php
/*
Plugin Name: Store Filter
Version: 1.9
Author: Your Name
*/

if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'store_filter_activate');
function store_filter_activate() {
    store_filter_register_post_type();
    store_filter_register_taxonomy();
    flush_rewrite_rules();
}

require_once plugin_dir_path(__FILE__) . 'acf-fields.php';

// 1. 註冊店家 Post Type
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
            'rewrite'     => array('slug' => 'store', 'with_front' => false),
            'supports'    => array('title', 'thumbnail', 'revisions'),
            'publicly_queryable' => true,
        ));
    }
    add_action('init', 'store_filter_register_post_type');
}

// 2. 註冊地區與產業分類
if (!function_exists('store_filter_register_taxonomy')) {
    function store_filter_register_taxonomy() {
        register_taxonomy('store_location', 'store', array(
            'hierarchical' => true,
            'labels' => array(
                'name' => '地區',
                'singular_name' => '地區',
                'menu_name' => '地區',
                'all_items' => '所有地區',
                'parent_item' => '上層地區',
                'add_new_item' => '新增地區'
            ),
            'rewrite' => array('slug' => 'store-location')
        ));

        register_taxonomy('store_industry', 'store', array(
            'hierarchical' => false,
            'labels' => array(
                'name' => '產業',
                'singular_name' => '產業',
                'menu_name' => '產業',
                'all_items' => '所有產業',
                'add_new_item' => '新增產業'
            ),
            'rewrite' => array('slug' => 'store-industry')
        ));
    }
    add_action('init', 'store_filter_register_taxonomy');
}

// 3. 前台載入腳本與樣式
if (!function_exists('store_filter_enqueue_scripts')) {
    function store_filter_enqueue_scripts() {
        $js_version = filemtime(plugin_dir_path(__FILE__) . 'assets/js/store-filter.js');
        $css_version = filemtime(plugin_dir_path(__FILE__) . 'assets/css/store-filter.css');

        wp_enqueue_script('store-filter-script', plugins_url('assets/js/store-filter.js', __FILE__), array('jquery'), $js_version, true);
        wp_localize_script('store-filter-script', 'storeAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('store_filter_nonce')
        ));

        wp_enqueue_style('store-filter-style', plugins_url('assets/css/store-filter.css', __FILE__), array(), $css_version);
    }
    add_action('wp_enqueue_scripts', 'store_filter_enqueue_scripts');
}

// 4. 前台篩選表單 Shortcode
if (!function_exists('store_filter_shortcode')) {
    function store_filter_shortcode($atts = []) {
        $atts = shortcode_atts(array('per_page' => 15), $atts, 'store_filter');
        $per_page = max(1, min(50, intval($atts['per_page'])));

        $cities = get_terms(array('taxonomy' => 'store_location', 'parent' => 0, 'hide_empty' => false));
        $industries = get_terms(array('taxonomy' => 'store_industry', 'hide_empty' => false));

        ob_start(); ?>
        <div class="store-filter-wrapper" data-per-page="<?php echo esc_attr($per_page); ?>">
            <form id="store-filter-form">
                <input type="hidden" id="store_per_page" name="per_page" value="<?php echo esc_attr($per_page); ?>">
                <select id="store_city" name="city">
                    <option value="">選擇縣市</option>
                    <?php foreach ($cities as $city) : ?>
                        <option value="<?php echo esc_attr($city->term_id); ?>"><?php echo esc_html($city->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="store_area" name="area" disabled>
                    <option value="">選擇區域</option>
                </select>
                <select id="store_industry" name="industry">
                    <option value="">全部產業</option>
                    <?php foreach ($industries as $industry) : ?>
                        <option value="<?php echo esc_attr($industry->term_id); ?>"><?php echo esc_html($industry->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" id="store_keyword" name="keyword" placeholder="搜尋名稱、電話、地址" />
                <button type="submit">搜尋</button>
            </form>
            <div id="store-list-results" class="store-list"></div>
            <div id="store-list-pagination"></div>
        </div>
        <?php return ob_get_clean();
    }
    add_shortcode('store_filter', 'store_filter_shortcode');
}

// 5. AJAX 篩選處理
if (!function_exists('store_filter_ajax_handler')) {
    function store_filter_ajax_handler() {
        // Verify nonce
        if (!check_ajax_referer('store_filter_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page and try again.'), 403);
            wp_die();
        }

        $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
        $city_id = isset($_POST['city']) ? intval($_POST['city']) : 0;
        $area_id = isset($_POST['area']) ? intval($_POST['area']) : 0;
        $industry_id = isset($_POST['industry']) ? intval($_POST['industry']) : 0;
        $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 15;
        $per_page = max(1, min(50, $per_page));

        if (!empty($keyword) && function_exists('store_filter_extend_search')) {
    add_filter('posts_search', 'store_filter_extend_search', 10, 2);
}

        $args = array(
            'post_type' => 'store',
            'posts_per_page' => $per_page,
            'paged' => $paged,
            's' => $keyword,
            'store_search' => true,
        );

        $tax_query = array('relation' => 'AND');

        if (!empty($industry_id)) {
            $tax_query[] = array(
                'taxonomy' => 'store_industry',
                'field' => 'term_id',
                'terms' => $industry_id,
            );
        }

        if (!empty($area_id)) {
            $tax_query[] = array(
                'taxonomy' => 'store_location',
                'field' => 'term_id',
                'terms' => $area_id
            );
        } elseif (!empty($city_id)) {
            $child_terms = get_terms(array(
                'taxonomy' => 'store_location',
                'parent' => $city_id,
                'fields' => 'ids',
                'hide_empty' => false
            ));
            $terms_to_query = array_merge(array($city_id), $child_terms);
            $tax_query[] = array(
                'taxonomy' => 'store_location',
                'field' => 'term_id',
                'terms' => $terms_to_query,
                'operator' => 'IN'
            );
        }

        if (count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
        }

        $query = new WP_Query($args);

       if (!empty($keyword) && function_exists('store_filter_extend_search')) {
    remove_filter('posts_search', 'store_filter_extend_search', 10, 2);
}

        $html = '';
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $store_phone = get_field('store_phone');
                $store_address = get_field('store_address');
                $bg_image_style = has_post_thumbnail() ? 'style="background-image: url(' . esc_url(get_the_post_thumbnail_url(get_the_ID(), 'large')) . ');"' : '';

                $html .= '<div class="store-item" ' . $bg_image_style . '>';
                $html .= '  <div class="store-item-content">';
                $html .= '    <div class="store-item-name">' . get_the_title() . '</div>';
                if ($store_phone) {
                    $html .= '    <div class="store-item-phone"><a href="tel:' . esc_attr(preg_replace('/[^\d+]/', '', $store_phone)) . '">TEL：' . esc_html($store_phone) . '</a></div>';
                }
                if ($store_address) {
                    $html .= '    <div class="store-item-address">地址：' . esc_html($store_address) . '</div>';
                }
                $html .= '    <div class="store-item-details-link"><a href="' . esc_url(get_permalink()) . '">詳細資訊</a></div>';
                $html .= '  </div>';
                $html .= '</div>';
            }
        } else {
            $html = '<p class="no-results">查無符合條件的店家。</p>';
        }
        wp_reset_postdata();

        $pagination_links = paginate_links(array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '?paged=%#%',
            'current' => max(1, $paged),
            'total' => $query->max_num_pages,
            'prev_text' => '«',
            'next_text' => '»',
            'type' => 'plain',
        ));

        wp_send_json_success(array('html' => $html, 'pagination' => $pagination_links));
        wp_die();
    }
    add_action('wp_ajax_store_filter', 'store_filter_ajax_handler');
    add_action('wp_ajax_nopriv_store_filter', 'store_filter_ajax_handler');
}
// 補上：取得子區域（地區）的 AJAX 處理
if (!function_exists('store_filter_get_districts_ajax')) {
    function store_filter_get_districts_ajax() {
        // Verify nonce
        if (!check_ajax_referer('store_filter_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page and try again.'), 403);
            wp_die();
        }
        $city_id = isset($_POST['city_id']) ? intval($_POST['city_id']) : 0;
        $data = array();

        if ($city_id > 0) {
            $terms = get_terms(array(
                'taxonomy' => 'store_location',
                'parent' => $city_id,
                'hide_empty' => false
            ));

            if (!is_wp_error($terms) && !empty($terms)) {
                foreach ($terms as $term) {
                    $data[] = array(
                        'term_id' => $term->term_id,
                        'name' => $term->name
                    );
                }
            }
        }

        wp_send_json_success($data);
        wp_die();
    }
    add_action('wp_ajax_store_filter_get_districts', 'store_filter_get_districts_ajax');
    add_action('wp_ajax_nopriv_store_filter_get_districts', 'store_filter_get_districts_ajax');
}

// 載入店家單一模板
add_filter('template_include', function ($template) {
    if (is_singular('store')) {
        $custom = plugin_dir_path(__FILE__) . 'single-store.php';
        if (file_exists($custom)) {
            return $custom;
        }
    }
    return $template;
});

/**
 * 擴展店家搜尋功能 - 支援搜尋店名、電話、地址
 * @param string $search 原始搜尋 SQL
 * @param WP_Query $query 查詢物件
 * @return string 修改後的搜尋 SQL
 */
if (!function_exists('store_filter_extend_search')) {
    function store_filter_extend_search($search, $query) {
        global $wpdb;
        
        // 確保只在特定查詢中生效
        if (empty($search) || !$query->get('store_search')) {
            return $search;
        }
        
        // 取得搜尋關鍵字
        $search_term = $query->get('s');
        if (empty($search_term)) {
            return $search;
        }
        
        // 清理搜尋關鍵字
        $search_term = sanitize_text_field($search_term);
        $search_term = trim($search_term);
        
        // 如果關鍵字為空或太短，返回原始搜尋
        if (strlen($search_term) < 2) {
            return $search;
        }
        
        // 準備搜尋關鍵字（用於 LIKE 查詢）
        $like_term = '%' . $wpdb->esc_like($search_term) . '%';
        
        // 構建自訂搜尋 SQL
        $custom_search = " AND (
            ({$wpdb->posts}.post_title LIKE %s)
            OR EXISTS (
                SELECT 1 FROM {$wpdb->postmeta} pm1 
                WHERE pm1.post_id = {$wpdb->posts}.ID 
                AND pm1.meta_key = 'store_phone' 
                AND pm1.meta_value LIKE %s
            )
            OR EXISTS (
                SELECT 1 FROM {$wpdb->postmeta} pm2 
                WHERE pm2.post_id = {$wpdb->posts}.ID 
                AND pm2.meta_key = 'store_address' 
                AND pm2.meta_value LIKE %s
            )
        )";
        
        // 準備 SQL 語句
        $prepared_search = $wpdb->prepare($custom_search, $like_term, $like_term, $like_term);
        
        return $prepared_search;
    }
}

/**
 * 輔助函數：清理搜尋關鍵字
 * @param string $keyword 原始關鍵字
 * @return string 清理後的關鍵字
 */
if (!function_exists('store_filter_sanitize_keyword')) {
    function store_filter_sanitize_keyword($keyword) {
        // 移除多餘空白
        $keyword = trim($keyword);
        
        // 移除可能的危險字符
        $keyword = preg_replace('/[<>"\']/', '', $keyword);
        
        // 限制長度
        if (strlen($keyword) > 100) {
            $keyword = substr($keyword, 0, 100);
        }
        
        return $keyword;
    }
}

/**
 * 錯誤處理和日誌記錄函數
 * @param string $message 錯誤訊息
 * @param string $context 錯誤上下文
 */
if (!function_exists('store_filter_log_error')) {
    function store_filter_log_error($message, $context = 'store_filter') {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[{$context}] {$message}");
        }
    }
}

/**
 * 改進的 AJAX 錯誤處理包裝函數
 * @param callable $callback 要執行的回調函數
 * @param string $action AJAX 動作名稱
 */
if (!function_exists('store_filter_ajax_wrapper')) {
    function store_filter_ajax_wrapper($callback, $action) {
        try {
            // 檢查 nonce
            if (!check_ajax_referer('store_filter_nonce', 'nonce', false)) {
                wp_send_json_error(array('message' => 'Security check failed'));
                return;
            }
            
            // 執行回調函數
            call_user_func($callback);
            
        } catch (Exception $e) {
            store_filter_log_error("Ajax error in {$action}: " . $e->getMessage());
            wp_send_json_error(array('message' => 'An error occurred while processing your request'));
        }
    }
}

