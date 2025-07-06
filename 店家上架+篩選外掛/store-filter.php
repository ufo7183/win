<?php
/*
Plugin Name: Store Filter
Version: 1.5
Author: Your Name
*/

if (!defined('ABSPATH')) exit;

// Include ACF fields
require_once plugin_dir_path(__FILE__) . 'acf-fields.php';

// 1. Register 'store' Post Type
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
            'supports'    => array('title', 'thumbnail', 'revisions'),
        ));
    }
    add_action('init', 'store_filter_register_post_type');
}

// 2. Register 'store_location' Taxonomy
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

// 3. Enqueue Frontend Scripts & Styles
if (!function_exists('store_filter_enqueue_scripts')) {
    function store_filter_enqueue_scripts() {
        wp_enqueue_style('store-filter-style', plugins_url('assets/css/store-filter.css', __FILE__), array(), '1.1');
        wp_enqueue_script('store-filter-script', plugins_url('assets/js/store-filter.js', __FILE__), array('jquery'), '1.1', true);
        wp_localize_script('store-filter-script', 'storeAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('store_filter_nonce')
        ));
    }
    add_action('wp_enqueue_scripts', 'store_filter_enqueue_scripts');
}

// 4. Shortcode for Store Filter
if (!function_exists('store_filter_shortcode')) {
    function store_filter_shortcode() {
        $cities = get_terms(array('taxonomy' => 'store_location', 'parent' => 0, 'hide_empty' => false));
        ob_start();
        ?>
        <div class="store-filter-wrapper">
            <form id="store-filter-form">
                <select id="store_city" name="city">
                    <option value="">選擇縣市</option>
                    <?php foreach ($cities as $city) : ?>
                        <option value="<?php echo esc_attr($city->term_id); ?>"><?php echo esc_html($city->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="store_area" name="area" disabled>
                    <option value="">選擇區域</option>
                </select>
                <input type="text" id="store_keyword" name="keyword" placeholder="搜尋名稱、電話、地址" />
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

// 5. AJAX Handler for Filtering
if (!function_exists('store_filter_ajax_handler')) {
    function store_filter_ajax_handler() {
        check_ajax_referer('store_filter_nonce', 'nonce');

        $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
        $city_id = isset($_POST['city']) ? intval($_POST['city']) : 0;
        $area_id = isset($_POST['area']) ? intval($_POST['area']) : 0;
        $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';

        if (!empty($keyword)) {
            add_filter('posts_search', 'store_filter_extend_search', 10, 2);
        }

        $args = array(
            'post_type'      => 'store',
            'posts_per_page' => 15,
            'paged'          => $paged,
            's'              => $keyword,
            'store_search'   => true, // Custom flag
        );

        $tax_query = array('relation' => 'AND');
        if (!empty($area_id)) {
            $tax_query[] = array('taxonomy' => 'store_location', 'field' => 'term_id', 'terms' => $area_id);
        } elseif (!empty($city_id)) {
            $child_terms = get_terms(array('taxonomy' => 'store_location', 'parent' => $city_id, 'fields' => 'ids', 'hide_empty' => false));
            $terms_to_query = array_merge(array($city_id), $child_terms);
            $tax_query[] = array('taxonomy' => 'store_location', 'field' => 'term_id', 'terms' => $terms_to_query, 'operator' => 'IN');
        }

        if (count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
        }

        $query = new WP_Query($args);

        if (!empty($keyword)) {
            remove_filter('posts_search', 'store_filter_extend_search', 10);
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
                $html .= '  </div>';
                $html .= '</div>';
            }
        } else {
            $html = '<p class="no-results">查無符合條件的店家。</p>';
        }
        wp_reset_postdata();

        $pagination_links = paginate_links(array(
            'base'      => add_query_arg('paged', '%#%'),
            'format'    => '?paged=%#%',
            'current'   => max(1, $paged),
            'total'     => $query->max_num_pages,
            'prev_text' => '«',
            'next_text' => '»',
            'type'      => 'plain',
        ));

        wp_send_json_success(array('html' => $html, 'pagination' => $pagination_links));
        wp_die();
    }
    add_action('wp_ajax_store_filter', 'store_filter_ajax_handler');
    add_action('wp_ajax_nopriv_store_filter', 'store_filter_ajax_handler');
}

// 6. Extend Search to Include Custom Fields
if (!function_exists('store_filter_extend_search')) {
    function store_filter_extend_search($search, $wp_query) {
        if (empty($wp_query->get('s')) || !isset($wp_query->query_vars['store_search'])) {
            return $search;
        }

        global $wpdb;
        $keyword = $wp_query->get('s');
        $keyword_like = '%' . $wpdb->esc_like($keyword) . '%';

        $search = $wpdb->prepare(
            " AND (($wpdb->posts.post_title LIKE %s) OR " .
            "(EXISTS (SELECT 1 FROM $wpdb->postmeta WHERE post_id = $wpdb->posts.ID AND meta_key = 'store_address' AND meta_value LIKE %s)) OR " .
            "(EXISTS (SELECT 1 FROM $wpdb->postmeta WHERE post_id = $wpdb->posts.ID AND meta_key = 'store_phone' AND meta_value LIKE %s)))",
            $keyword_like, $keyword_like, $keyword_like
        );

        return $search;
    }
}

// 7. AJAX: Get Child Terms (Areas)
if (!function_exists('store_filter_get_districts_ajax')) {
    function store_filter_get_districts_ajax() {
        check_ajax_referer('store_filter_nonce', 'nonce');
        $city_id = isset($_POST['city_id']) ? intval($_POST['city_id']) : 0;
        $data = array();
        if ($city_id > 0) {
            $terms = get_terms(array('taxonomy' => 'store_location', 'parent' => $city_id, 'hide_empty' => false));
            if (!is_wp_error($terms) && !empty($terms)) {
                foreach ($terms as $t) {
                    $data[] = array('term_id' => $t->term_id, 'name' => $t->name);
                }
            }
        }
        wp_send_json_success($data);
        wp_die();
    }
    add_action('wp_ajax_store_filter_get_districts', 'store_filter_get_districts_ajax');
    add_action('wp_ajax_nopriv_store_filter_get_districts', 'store_filter_get_districts_ajax');
}

// 8. Enqueue Admin Scripts & Styles
if (!function_exists('store_filter_admin_enqueue_scripts')) {
    function store_filter_admin_enqueue_scripts($hook) {
        global $post;
        if (($hook == 'post.php' || $hook == 'post-new.php') && isset($post->post_type) && $post->post_type === 'store') {
            wp_enqueue_style('dashicons');
            wp_enqueue_style('store-admin-style', plugins_url('assets/css/store-admin.css', __FILE__), array(), '1.0');
            wp_enqueue_script('store-admin-script', plugins_url('assets/js/store-admin.js', __FILE__), array('jquery'), '1.0', true);
        }
    }
    add_action('admin_enqueue_scripts', 'store_filter_admin_enqueue_scripts');
}

// 9. Customize Admin UI for 'store' Post Type
if (!function_exists('store_filter_customize_admin_ui')) {
    function store_filter_customize_admin_ui() {
        remove_meta_box('postimagediv', 'store', 'side');
        add_meta_box('postimagediv', __('店家照片'), 'post_thumbnail_meta_box', 'store', 'normal', 'high');
    }
    add_action('add_meta_boxes_store', 'store_filter_customize_admin_ui');

    function store_filter_featured_image_text($content, $post_id) {
        if (get_post_type($post_id) === 'store') {
            $content = str_replace('Set featured image', '設定店家照片', $content);
            $content = str_replace('Remove featured image', '移除店家照片', $content);
        }
        return $content;
    }
    add_filter('admin_post_thumbnail_html', 'store_filter_featured_image_text', 10, 2);
}
