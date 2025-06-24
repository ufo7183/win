// ========== 功能一：註冊 Shortcode ==========
add_shortcode('your_ajax_tabs', 'your_ajax_tabs_shortcode_function');
function your_ajax_tabs_shortcode_function($atts) {
    $atts = shortcode_atts(array(
        'post_type' => 'teams',
        'taxonomy'  => 'teams-category',
        'query_id'  => '',
    ), $atts, 'your_ajax_tabs');
    
    // 移除所有登入相關的檢查

    $terms = get_terms(array(
        'taxonomy'   => $atts['taxonomy'],
        'hide_empty' => true,
    ));

    ob_start();

    if (!empty($terms) && !is_wp_error($terms)) {
        // 我們把 taxonomy 也存入 data-* 屬性，方便 JS 讀取
        echo '<ul class="yat-custom-tabs-container" data-query-id="' . esc_attr($atts['query_id']) . '" data-taxonomy="' . esc_attr($atts['taxonomy']) . '">';
        echo '<li class="yat-tab-item yat-active"><a href="#" data-term-id="all">全部</a></li>';
        foreach ($terms as $term) {
            echo '<li class="yat-tab-item"><a href="#" data-term-id="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</a></li>';
        }
        echo '</ul>';
    }

    return ob_get_clean();
}


// ========== 功能二：啟用 PHP Session ==========
add_action('init', 'your_ajax_tabs_session_start');
function your_ajax_tabs_session_start() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Reset tab state on logout
    if (isset($_GET['loggedout']) && $_GET['loggedout'] === 'true') {
        unset($_SESSION['yat_current_term_id']);
        unset($_SESSION['yat_current_taxonomy']);
    }
}


// ========== 功能三：後端 AJAX 處理函式 ==========
add_action('wp_ajax_your_filter_action', 'your_ajax_tabs_filter_handler');
add_action('wp_ajax_nopriv_your_filter_action', 'your_ajax_tabs_filter_handler');
function your_ajax_tabs_filter_handler() {
    // 檢查 nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'your_ajax_nonce')) {
        wp_send_json_error('Invalid nonce');
    }

    $term_id = isset($_POST['term_id']) ? sanitize_text_field($_POST['term_id']) : 'all';
    $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : 'teams-category';
    $query_id = isset($_POST['query_id']) ? sanitize_text_field($_POST['query_id']) : '';

    // 直接返回篩選後的內容
    ob_start();
    
    $args = array(
        'post_type'      => 'teams',
        'posts_per_page' => 12,
        'post_status'    => 'publish',
    );

    // 如果不是全部，添加分類篩選
    if ($term_id !== 'all') {
        $args['tax_query'] = array(
            array(
                'taxonomy' => $taxonomy,
                'field'    => 'term_id',
                'terms'    => $term_id,
            ),
        );
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            // 這裡使用你的文章模板部分
            ?>
            <div class="team-item">
                <h3><?php the_title(); ?></h3>
                <div class="team-content">
                    <?php the_content(); ?>
                </div>
            </div>
            <?php
        }
        wp_reset_postdata();
    } else {
        echo '<p>沒有找到相關文章</p>';
    }

    $html = ob_get_clean();
    
    wp_send_json_success(array(
        'html' => $html,
        'message' => '篩選成功',
        'term_id' => $term_id,
        'found_posts' => $query->found_posts
    ));
}


// ========== 功能四：掛載到 Elementor Query Hook ==========
add_action('elementor/query/my-first-loop', function($query) {
    // 設置每頁顯示12個項目
    $query->set('posts_per_page', 12);
    $query->set('nopaging', false);
    
    // 檢查 URL 中的 category 參數
    $category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
    
    if (!empty($category) && $category !== 'all') {
        $tax_query = array(
            array(
                'taxonomy' => 'teams-category',
                'field'    => 'term_id',
                'terms'    => $category,
                'operator' => 'IN'
            )
        );
        $query->set('tax_query', $tax_query);
    }
    
    // 確保查詢參數被正確設置
    $query->set('post_status', 'publish');
    $query->set('ignore_sticky_posts', true);
    
    // 記錄最終查詢參數
    error_log('Final Query Args: ' . print_r($query->query_vars, true));
});

// ========== 添加後台除錯日誌頁面 ==========
add_action('admin_menu', 'add_debug_log_page');
function add_debug_log_page() {
    add_submenu_page(
        'tools.php',
        '團隊頁面除錯日誌',
        '團隊頁面除錯',
        'manage_options',
        'team-debug-log',
        'display_team_debug_log'
    );
}

function display_team_debug_log() {
    echo '<div class="wrap">';
    echo '<h1>團隊頁面除錯日誌</h1>';
    
    // 顯示當前設置
    echo '<h2>當前設置</h2>';
    echo '<p>每頁顯示文章數: ' . get_option('posts_per_page') . '</p>';
    
    // 顯示Session狀態
    echo '<h2>Session 狀態</h2>';
    echo '<pre>';
    if (isset($_SESSION['yat_current_term_id'])) {
        echo '當前分類ID: ' . esc_html($_SESSION['yat_current_term_id']) . "\n";
        echo '當前分類法: ' . esc_html($_SESSION['yat_current_taxonomy']) . "\n";
    } else {
        echo 'Session 未設置' . "\n";
    }
    echo '</pre>';
    
    // 顯示除錯日誌
    echo '<h2>除錯日誌</h2>';
    $debug_log = ini_get('error_log');
    if (file_exists($debug_log)) {
        $log_content = file_get_contents($debug_log);
        echo '<pre>' . esc_html($log_content) . '</pre>';
    } else {
        echo '<p>找不到除錯日誌文件: ' . esc_html($debug_log) . '</p>';
    }
    
    echo '</div>';
}


// ========== 功能五：載入 JS 和 CSS 檔案 ==========
add_action('wp_enqueue_scripts', 'your_ajax_tabs_enqueue_scripts');
function your_ajax_tabs_enqueue_scripts() {
    // 載入 JS

    // 傳遞資料給 JS
    // 載入 JS
    wp_enqueue_script(
        'yat-custom-tabs-script',
        get_stylesheet_directory_uri() . '/tab切換JS.js',
        array('jquery'),
        '1.1.0', // 更新版本號
        true
    );
    
    // 添加內聯樣式來避免衝突
    $custom_css = "
        .yat-custom-tabs-container { margin: 20px 0; }
        .yat-custom-tabs-container .yat-tab-item { display: inline-block; margin-right: 10px; }
        .yat-custom-tabs-container .yat-tab-item a { text-decoration: none; padding: 5px 10px; }
        .yat-custom-tabs-container .yat-tab-item.yat-active a { font-weight: bold; }
    ";
    wp_add_inline_style('elementor-frontend', $custom_css);
    
    // 傳遞資料給 JS
    wp_localize_script(
        'yat-custom-tabs-script',
        'your_ajax_obj',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('your_ajax_nonce')
        )
    );
    // 載入 CSS
    wp_enqueue_style(
        'your-ajax-tabs-style',
        get_stylesheet_directory_uri() . '/tab切換CSS.css',
        array(),
        '1.0.1'
    );
    
    // 確保 Elementor 前端腳本已載入
    if (class_exists('\Elementor\Plugin')) {
        wp_enqueue_script('elementor-frontend');
    }
}