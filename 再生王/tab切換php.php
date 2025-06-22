// ========== 功能一：註冊 Shortcode ==========
add_shortcode('your_ajax_tabs', 'your_ajax_tabs_shortcode_function');
function your_ajax_tabs_shortcode_function($atts) {
    $atts = shortcode_atts(array(
        'post_type' => 'teams',
        'taxonomy'  => 'teams-category',
        'query_id'  => '',
    ), $atts, 'your_ajax_tabs');

    $terms = get_terms(array(
        'taxonomy'   => $atts['taxonomy'],
        'hide_empty' => true,
    ));

    ob_start();

    if (!empty($terms) && !is_wp_error($terms)) {
        // 我們把 taxonomy 也存入 data-* 屬性，方便 JS 讀取
        echo '<ul class="your-ajax-tabs-container" data-query-id="' . esc_attr($atts['query_id']) . '" data-taxonomy="' . esc_attr($atts['taxonomy']) . '">';
        echo '<li class="active"><a href="#" data-term-id="all">全部</a></li>';
        foreach ($terms as $term) {
            echo '<li><a href="#" data-term-id="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</a></li>';
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
}


// ========== 功能三：後端 AJAX 處理函式 ==========
add_action('wp_ajax_your_filter_action', 'your_ajax_tabs_filter_handler');
add_action('wp_ajax_nopriv_your_filter_action', 'your_ajax_tabs_filter_handler');
function your_ajax_tabs_filter_handler() {
    check_ajax_referer('your_ajax_nonce', 'nonce');

    $term_id = isset($_POST['term_id']) ? sanitize_text_field($_POST['term_id']) : 'all';
    $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : 'category';

    $_SESSION['yat_current_term_id'] = $term_id;
    $_SESSION['yat_current_taxonomy'] = $taxonomy;
    
    // 除錯完畢後，可以移除下方的 debug_data 和括號中的內容
    $debug_data = array(
        'message' => 'Session updated successfully',
        'session_term_id' => $_SESSION['yat_current_term_id'],
        'session_taxonomy' => $_SESSION['yat_current_taxonomy']
    );
    wp_send_json_success($debug_data);
}


// ========== 功能四：掛載到 Elementor Query Hook (最關鍵！) ==========
add_action('elementor/query/my-first-loop', function($query) {
    // 強制設置每頁顯示12個項目
    $query->set('posts_per_page', 12);
    $query->set('nopaging', false);
    
    // 添加除錯日誌
    $debug_message = 'Elementor Query Hook Triggered - ';
    $debug_message .= 'Current Term ID: ' . (isset($_SESSION['yat_current_term_id']) ? $_SESSION['yat_current_term_id'] : 'not set') . ', ';
    $debug_message .= 'Current Taxonomy: ' . (isset($_SESSION['yat_current_taxonomy']) ? $_SESSION['yat_current_taxonomy'] : 'not set') . ', ';
    $debug_message .= 'Posts Per Page: ' . $query->get('posts_per_page');
    
    // 記錄到WordPress除錯日誌
    error_log($debug_message);
    
    if (isset($_SESSION['yat_current_term_id']) && $_SESSION['yat_current_term_id'] !== 'all') {
        $tax_query = $query->get('tax_query');
        if (!is_array($tax_query)) {
            $tax_query = array();
        }
        $tax_query[] = array(
            'taxonomy' => $_SESSION['yat_current_taxonomy'],
            'field'    => 'term_id',
            'terms'    => $_SESSION['yat_current_term_id'],
            'operator' => 'IN'
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
    wp_enqueue_script(
        'your-ajax-tabs-script',
        get_stylesheet_directory_uri() . '/tab切換JS.js',
        array('jquery'),
        '1.0.2', // 提升版本號，強制瀏覽器更新
        true
    );
    // 傳遞資料給 JS
    wp_localize_script(
        'your-ajax-tabs-script',
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