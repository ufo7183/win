<?php
/*
Plugin Name: store Filter
Version: 1.0
Author: Your Name
*/

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

// 4. 短代碼：搜尋表單
if (!function_exists('store_search_bar_shortcode')) {
    function store_search_bar_shortcode($atts) {
        $cities = get_terms(array(
            'taxonomy'   => 'store_location',
            'parent'     => 0,
            'hide_empty' => false
        ));
        ob_start();
        ?>
<div class="myplugin-wrapper">
    <div class="store-filter-container">
        <form id="store-filter-form">
            <div class="store-filter-row">
                <!-- 縣市 -->
                <div class="store-filter-field city">
                    <select id="store_city" name="store_city">
                        <option value="">選擇縣市</option>
                        <?php if ($cities && !is_wp_error($cities)) : ?>
                            <?php foreach($cities as $city) : ?>
                                <option value="<?php echo esc_attr($city->term_id); ?>">
                                    <?php echo esc_html($city->name); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <!-- 區域 -->
                <div class="store-filter-field area">
                    <select id="store_area" name="store_area" disabled>
                        <option value="">選擇區域</option>
                    </select>
                </div>
                <!-- 搜尋關鍵字 -->
                <div class="search-box">
                    <div class="ser-box">
                        <i class="fa fa-search search-icon"></i>
                        <input type="text" id="store_keyword" placeholder="搜尋店家名稱" />
                        <button type="submit" id="store-filter-submit" class="button">搜尋</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
        <?php
        return ob_get_clean();
    }
    add_shortcode('store_search_bar', 'store_search_bar_shortcode');
}

// 5. 短代碼：店家列表
if (!function_exists('store_list_shortcode')) {
    function store_list_shortcode($atts) {
        ob_start();
        ?>
<div class="myplugin-wrapper">
    <div id="store-list-container" class="store-list-container">
        <div class="store-initial-message">搜尋您附近的認證店家</div>
    </div>
    <button id="load-more" class="load-more hidden">載入更多</button>
</div>
        <?php
        return ob_get_clean();
    }
    add_shortcode('store_list', 'store_list_shortcode');
}

// 6. 產生列表 HTML
if (!function_exists('store_filter_generate_list')) {
    function store_filter_generate_list($city_id = 0, $area_id = 0, $keyword = '', $offset = 0) {
        $args = array(
            'post_type'      => 'store',
            'posts_per_page' => 25,
            'offset'         => $offset
        );

        // tax query
        $tax_query = array();
        if (!empty($area_id)) {
            // 如果有選擇區域，直接用區域ID查詢
            $tax_query[] = array(
                'taxonomy' => 'store_location',
                'field'    => 'term_id',
                'terms'    => $area_id
            );
        } elseif (!empty($city_id)) {
            // 如果只選擇縣市，則查詢該縣市以及其下所有區域
            $child_terms = get_terms(array(
                'taxonomy'  => 'store_location',
                'parent'    => $city_id,
                'fields'    => 'ids',
                'hide_empty'=> false
            ));

            $terms_to_query = array($city_id); // 將城市ID加入查詢
            if (!is_wp_error($child_terms) && !empty($child_terms)) {
                $terms_to_query = array_merge($terms_to_query, $child_terms); // 合併子區域ID
            }

            $tax_query[] = array(
                'taxonomy' => 'store_location',
                'field'    => 'term_id',
                'terms'    => $terms_to_query,
                'operator' => 'IN'
            );
        }

        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }

        // 關鍵字
        if (!empty($keyword)) {
            $args['s'] = sanitize_text_field($keyword);
        }

        // --- Test/Debug Function ---
        // 當 WP_DEBUG 啟用時，這段程式碼會將查詢參數記錄到 debug.log
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('--- Store Filter Debug ---');
            error_log('Query Args: ' . print_r($args, true));
        }
        // --- End Test/Debug Function ---

        $query = new WP_Query($args);
        // 計算總量
        $total_query = new WP_Query(array_merge($args, array('posts_per_page' => -1)));
        $total = $total_query->post_count;

        if (!$query->have_posts()) {
            return array('success' => false);
        }

        ob_start();
        while ($query->have_posts()) {
            $query->the_post();
            $address      = get_field('store_address') ?: '';
            $address_url  = get_field('store_address_url') ?: '';
            $phone        = get_field('store_phone') ?: '';
            $phone_url    = get_field('store_phone_url') ?: '';
            ?>
            <div class="store-item">
                <h3 class="store-title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h3>
                <div class="store-address">
                    <?php if($address_url): ?>
                        <a href="<?php echo esc_url($address_url); ?>" target="_blank">
                            <?php echo esc_html($address); ?>
                        </a>
                    <?php else: ?>
                        <?php echo esc_html($address); ?>
                    <?php endif; ?>
                </div>
                <div class="store-phone">
                    <?php if($phone_url): ?>
                        <a href="<?php echo esc_url($phone_url); ?>">
                            <?php echo esc_html($phone); ?>
                        </a>
                    <?php else: ?>
                        <?php echo esc_html($phone); ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
        wp_reset_postdata();

        $html = ob_get_clean();

        return array(
            'success' => true,
            'data' => array(
                'html'  => $html,
                'total' => $total
            )
        );
    }
}

// 7. AJAX：取得子區域
if (!function_exists('store_filter_get_districts_ajax')) {
    function store_filter_get_districts_ajax() {
        check_ajax_referer('store_filter_nonce', 'security');
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
                    $data[] = array(
                        'term_id' => $t->term_id,
                        'name'    => $t->name
                    );
                }
            }
        }
        wp_send_json_success($data);
        wp_die();
    }
    add_action('wp_ajax_store_filter_get_districts','store_filter_get_districts_ajax');
    add_action('wp_ajax_nopriv_store_filter_get_districts','store_filter_get_districts_ajax');
}

// 8. AJAX：篩選
if (!function_exists('store_filter_ajax_search')) {
    function store_filter_ajax_search() {
        check_ajax_referer('store_filter_nonce','security');
        $city_id = isset($_POST['city_id']) ? intval($_POST['city_id']) : 0;
        $area_id = isset($_POST['area_id']) ? intval($_POST['area_id']) : 0;
        $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
        $offset  = isset($_POST['offset']) ? intval($_POST['offset']) : 0;

        $result = store_filter_generate_list($city_id, $area_id, $keyword, $offset);

        if($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error();
        }
        wp_die();
    }
    add_action('wp_ajax_store_filter','store_filter_ajax_search');
    add_action('wp_ajax_nopriv_store_filter','store_filter_ajax_search');
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
