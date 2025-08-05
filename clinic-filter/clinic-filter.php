<?php
/*
Plugin Name: Clinic Filter
Version: 1.0.2
Author: Your Name
*/

if (!defined('ABSPATH')) exit;

/* ----------------------------------------------------------
 * 1. 註冊「診所」Post Type
 * ---------------------------------------------------------- */
add_action('init', function () {
    register_post_type('clinic', [
        'labels' => [
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
            'not_found_in_trash' => '回收桶中沒有診所',
        ],
        'public'      => true,
        'has_archive' => true,
        'rewrite'     => ['slug' => 'clinic'],
        'supports'    => ['title', 'thumbnail'],
    ]);

    /* ACF 欄位 */
    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group([
            'key'    => 'group_clinic_details',
            'title'  => '診所詳細資料',
            'fields' => [
                ['key' => 'field_address',       'label' => '地址',       'name' => 'address',       'type' => 'text', 'required' => 1],
                ['key' => 'field_phone',         'label' => '電話',       'name' => 'phone',         'type' => 'text', 'required' => 0],
                ['key' => 'field_store_website', 'label' => '店家網址',   'name' => 'store_website', 'type' => 'url',  'required' => 0],
            ],
            'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'clinic']]],
            'position' => 'acf_after_title',
        ]);
    }
});

/* ----------------------------------------------------------
 * 2. 註冊「診所地區」Taxonomy
 * ---------------------------------------------------------- */
add_action('init', function () {
    register_taxonomy('clinic_location', ['clinic'], [
        'hierarchical' => true,
        'labels'       => [
            'name'          => '地區',
            'singular_name' => '地區',
            'search_items'  => '搜尋地區',
            'all_items'     => '所有地區',
            'edit_item'     => '編輯地區',
            'update_item'   => '更新地區',
            'add_new_item'  => '新增地區',
            'new_item_name' => '新地區名稱',
            'menu_name'     => '地區',
        ],
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'clinic-location'],
    ]);
});

/* 移除預設編輯器 */
add_action('admin_init', function () {
    remove_post_type_support('clinic', 'editor');
    remove_post_type_support('clinic', 'excerpt');
    remove_post_type_support('clinic', 'comments');
    remove_post_type_support('clinic', 'trackbacks');
    remove_post_type_support('clinic', 'custom-fields');
    remove_post_type_support('clinic', 'revisions');
    remove_post_type_support('clinic', 'page-attributes');
    remove_post_type_support('clinic', 'post-formats');
});

/* ----------------------------------------------------------
 * 3. 前端資源
 * ---------------------------------------------------------- */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('clinic-filter-style',  plugins_url('assets/css/clinic-filter.css', __FILE__), [], '1.0.2');
    wp_enqueue_script('clinic-filter-script', plugins_url('assets/js/clinic-filter.js',  __FILE__), ['jquery'], '1.0.2', true);
    wp_localize_script('clinic-filter-script', 'clinicAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('clinic_filter_nonce'),
    ]);
});

/* ----------------------------------------------------------
 * 4. 短代碼：搜尋表單
 * ---------------------------------------------------------- */
add_shortcode('clinic_search_bar', function () {
    ob_start();
    $regions = get_terms(['taxonomy' => 'clinic_location', 'parent' => 0, 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC']);
    $cities  = get_terms(['taxonomy' => 'clinic_location', 'parent__not_in' => [0], 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC']);
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
                <select class="form-select" id="clinic-region">
                    <option value="">選擇地區</option>
                    <?php foreach ($regions as $region) : ?>
                        <option value="<?= esc_attr($region->term_id) ?>"><?= esc_html($region->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select class="form-select" id="clinic-city">
                    <option value="">選擇縣市</option>
                    <?php foreach ($cities as $city) : ?>
                        <option value="<?= esc_attr($city->term_id) ?>"><?= esc_html($city->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
});

/* ----------------------------------------------------------
 * 5. 短代碼：診所列表
 * ---------------------------------------------------------- */
add_shortcode('clinic_list', function ($atts) {
    $atts = shortcode_atts(['posts_per_page' => 25], $atts, 'clinic_list');
    ob_start();
    $initial = clinic_filter_generate_list(0, 0, '', 0);
    ?>
    <div id="clinic-list-container"><?= is_array($initial) ? $initial['data'] : $initial ?></div>
    <div class="pagination-container mt-4"><?= is_array($initial) ? $initial['pagination'] : '' ?></div>
    <?php
    return ob_get_clean();
});

/* ----------------------------------------------------------
 * 6. 產生列表 HTML & 分頁
 * ---------------------------------------------------------- */
function clinic_filter_generate_list($city_id = 0, $area_id = 0, $keyword = '', $offset = 0) {
    $city_id = intval($city_id);
    $area_id = intval($area_id);
    $keyword = sanitize_text_field($keyword);

    $paged = 1;
    if (is_numeric($offset) && $offset > 0) {
        $paged = ceil(($offset + 25) / 25);
    } elseif (isset($_GET['paged']) && is_numeric($_GET['paged'])) {
        $paged = max(1, intval($_GET['paged']));
    }

    $args = [
        'post_type'      => 'clinic',
        'posts_per_page' => 25,
        'paged'          => $paged,
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
        'post_status'    => 'publish',
    ];

    if ($city_id > 0) {
        $args['tax_query'] = [['taxonomy' => 'clinic_location', 'field' => 'term_id', 'terms' => $city_id]];
    } elseif ($area_id > 0) {
        $args['tax_query'] = [['taxonomy' => 'clinic_location', 'field' => 'term_id', 'terms' => $area_id, 'include_children' => true]];
    }

    if (!empty($keyword)) {
        $args['s'] = $keyword;
    }

    $query = new WP_Query($args);
    $total_query = new WP_Query(array_merge($args, ['posts_per_page' => -1, 'paged' => 1]));
    $total = $total_query->found_posts;
    $max_pages = $query->max_num_pages;

    ob_start();
    echo '<div class="clinic-list">';
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $address       = get_field('address', get_the_ID());
            $phone         = get_field('phone', get_the_ID());
            $store_website = get_field('store_website', get_the_ID());

            $locations = get_the_terms(get_the_ID(), 'clinic_location');
            $city_name = '';
            if ($locations && !is_wp_error($locations)) {
                foreach ($locations as $loc) {
                    if ($loc->parent != 0) {
                        $city_name = $loc->name;
                        break;
                    }
                }
            }
            ?>
            <a href="<?= $store_website ? esc_url($store_website) : '#' ?>" class="clinic-item-link" <?= $store_website ? 'target="_blank"' : '' ?>>
                <div class="clinic-item">
                    <div class="clinic-left">
                        <div class="clinic-name">
                            <?php if ($city_name) : ?>
                                <span class="s-item-city"><?= esc_html($city_name) ?></span>
                            <?php endif; ?>
                            <?php the_title(); ?>
                        </div>
                    </div>
                    <div class="clinic-right">
                        <div class="clinic-info">
                            <?php if ($address) : ?>
                                <div class="clinic-address"><?= esc_html($address) ?></div>
                            <?php endif; ?>
                            <?php if ($phone) : ?>
                                <div class="clinic-phone"><?= esc_html($phone) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </a>
            <?php
        }
    } else {
        echo '<div class="alert alert-info">' . (!empty($keyword) ? '搜尋不到此診所' : '此地區暫無認證診所') . '</div>';
    }
    echo '</div>';
    wp_reset_postdata();

    $pagination = '';
    if ($max_pages > 1) {
        $pagination = '<div class="clinic-pagination">';
        $current = max(1, $paged);
        if ($current > 1) {
            $pagination .= '<a href="#" class="page-numbers prev" data-page="' . ($current - 1) . '">&laquo; 上一頁</a>';
        }
        for ($i = 1; $i <= $max_pages; $i++) {
            $pagination .= $i == $current
                ? '<span class="page-numbers current">' . $i . '</span>'
                : '<a href="#" class="page-numbers" data-page="' . $i . '">' . $i . '</a>';
        }
        if ($current < $max_pages) {
            $pagination .= '<a href="#" class="page-numbers next" data-page="' . ($current + 1) . '">下一頁 &raquo;</a>';
        }
        $pagination .= '</div>';
    }

    $response = [
        'success'    => $query->have_posts(),
        'data'       => ob_get_clean(),
        'pagination' => $pagination,
        'current_page' => $paged,
        'max_pages'  => $max_pages,
    ];

    if (wp_doing_ajax()) {
        wp_send_json_success($response);
    }
    return $response;
}

/* ----------------------------------------------------------
 * 7. AJAX：取得子分類（縣市）
 * ---------------------------------------------------------- */
add_action('wp_ajax_clinic_filter_get_districts', function () {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'clinic_filter_nonce')) {
        wp_send_json_error('Invalid nonce');
    }

    $region_id = isset($_POST['city_id']) ? sanitize_text_field($_POST['city_id']) : '';
    $args = [
        'taxonomy'   => 'clinic_location',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ];

    if ($region_id === '' || $region_id === 'all') {
        $args['parent__not_in'] = [0];
    } elseif (is_numeric($region_id)) {
        $args['parent'] = intval($region_id);
    } else {
        wp_send_json_success('');
        return;
    }

    $cities = get_terms($args);
    $options = '';
    if (!is_wp_error($cities) && !empty($cities)) {
        foreach ($cities as $city) {
            $options .= sprintf('<option value="%s">%s</option>', esc_attr($city->term_id), esc_html($city->name));
        }
    }
    wp_send_json_success($options);
});
add_action('wp_ajax_nopriv_clinic_filter_get_districts', 'clinic_filter_get_districts');

/* ----------------------------------------------------------
 * 8. AJAX：篩選診所
 * ---------------------------------------------------------- */
add_action('wp_ajax_clinic_filter', function () {
    check_ajax_referer('clinic_filter_nonce', 'nonce');
    $city_id = isset($_POST['city_id'])  ? intval($_POST['city_id'])  : 0;
    $area_id = isset($_POST['area_id'])  ? intval($_POST['area_id'])  : 0;
    $keyword = isset($_POST['keyword'])  ? sanitize_text_field($_POST['keyword']) : '';
    $paged   = isset($_POST['paged'])    ? max(1, intval($_POST['paged'])) : 1;

    $result = clinic_filter_generate_list($city_id, $area_id, $keyword, ($paged - 1) * 25);
    wp_send_json_success($result);
});
add_action('wp_ajax_nopriv_clinic_filter', 'clinic_filter');