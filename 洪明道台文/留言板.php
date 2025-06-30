/**
 * 心靈得留言板系統
 * 基於WooCommerce會員系統的巢狀留言板
 */

// 防止直接訪問
if (!defined('ABSPATH')) {
    exit;
}

class AngmindeMessageBoard {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_like_comment', array($this, 'handle_like_comment'));
        add_action('wp_ajax_nopriv_like_comment', array($this, 'handle_like_comment'));
        add_action('wp_ajax_add_comment', array($this, 'handle_add_comment'));
        add_action('wp_ajax_nopriv_add_comment', array($this, 'handle_add_comment'));

        
        // 註冊Shortcode
        add_shortcode('angminde_message_board', array($this, 'render_message_board'));
        
        // 登入後重定向
        add_filter('woocommerce_login_redirect', array($this, 'login_redirect'));
    }
    
    public function init() {
        // 建立資料表
        $this->create_tables();
    }
    
    /**
     * 建立所需的資料表
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // 留言表
        $table_name = $wpdb->prefix . 'angminde_messages';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            parent_id mediumint(9) DEFAULT 0,
            message text NOT NULL,
            like_count int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            status varchar(20) DEFAULT 'published',
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY parent_id (parent_id)
        ) $charset_collate;";
        
        // 按讚記錄表
        $likes_table = $wpdb->prefix . 'angminde_message_likes';
        $likes_sql = "CREATE TABLE $likes_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            message_id mediumint(9) NOT NULL,
            user_id bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY message_user (message_id, user_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        dbDelta($likes_sql);
    }
    
    /**
     * 載入腳本和樣式
     */
    public function enqueue_scripts() {
        wp_enqueue_script('angminde-message-board-js', plugin_dir_url(__FILE__) . 'angminde-message-board.js', array('jquery'), '1.0.0', true);
        wp_enqueue_style('angminde-message-board-css', plugin_dir_url(__FILE__) . 'angminde-message-board.css', array(), '1.0.0');
        
        // 傳遞AJAX URL和nonce到JavaScript
        wp_localize_script('angminde-message-board-js', 'angminde_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('angminde_nonce'),
            'login_url' => wc_get_account_endpoint_url('login'),
            'is_logged_in' => is_user_logged_in()
        ));
    }
    
    /**
     * 登入後重定向至留言板頁面
     */
    public function login_redirect($redirect) {
        return 'https://angminde.tw/message/';
    }
    
    /**
     * 主要Shortcode渲染函數
     */
    public function render_message_board($atts) {
        $atts = shortcode_atts(array(
            'view_for' => 'all' // all 或 members
        ), $atts);
        
        // 檢查觀看權限
        if ($atts['view_for'] === 'members' && !is_user_logged_in()) {
            return '<p>此功能僅限會員使用，請先登入。</p>';
        }
        
        ob_start();
        
        // 渲染輸入框
        $this->render_input_box();
        
        // 渲染留言列表
        $this->render_messages_list();
        
        return ob_get_clean();
    }
    
    /**
     * 渲染輸入框
     */
    private function render_input_box() {
        $is_logged_in = is_user_logged_in();
        $login_url = wc_get_account_endpoint_url('login');
        
        echo '<div class="angminde-message-board-container">';
        echo '<div class="angminde-input-container">';
        
        if (!$is_logged_in) {
            // 未登入狀態 - 整個框為連結
            echo '<a href="' . esc_url($login_url) . '" class="angminde-input-link">';
            echo '<div class="angminde-input-box">';
            echo '<div class="angminde-placeholder">你想說什麼？</div>';
            echo '<div class="angminde-login-btn">登入</div>';
            echo '</div>';
            echo '</a>';
        } else {
            // 已登入狀態 - 真正的表單
            echo '<div class="angminde-input-form">';
            echo '<div class="angminde-input-box">';
            echo '<textarea class="angminde-message-input" placeholder="你想說什麼？" maxlength="1000"></textarea>';
            echo '<button type="button" class="angminde-submit-btn">發送</button>';
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * 渲染留言列表
     */
    private function render_messages_list() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'angminde_messages';
        
        // 取得主留言（parent_id = 0）
        $main_messages = $wpdb->get_results(
            "SELECT * FROM $table_name WHERE parent_id = 0 AND status = 'published' ORDER BY created_at DESC"
        );
        
        echo '<div class="angminde-messages-container">';
        
        foreach ($main_messages as $message) {
            $this->render_single_message($message, 0);
        }
        
        echo '</div>';
        echo '</div>'; // 關閉 angminde-message-board-container
    }
    
    /**
     * 渲染單一留言
     */
    private function render_single_message($message, $level = 0) {
        global $wpdb;
        
        $user = get_user_by('id', $message->user_id);
        $is_admin = user_can($message->user_id, 'manage_options');
        $avatar_url = $this->get_user_avatar($message->user_id);
        $like_count = $this->get_like_count($message->id);
        $user_liked = $this->user_has_liked($message->id);
        $reply_count = $this->get_reply_count($message->id);
        
        // 判斷奇偶層級來選擇按讚圖示
        $like_icon = ($level % 2 == 0) ? 
            'https://angminde.tw/wp-content/uploads/2025/06/cb893407c3aea97c95c75398721b0847.svg' : 
            'https://angminde.tw/wp-content/uploads/2025/06/d37728ea10963397495efe8946e37ce9.svg';
        
        echo '<div class="angminde-message-item" data-message-id="' . $message->id . '" data-level="' . $level . '">';
        echo '<div class="angminde-message-main">';
        
        // 用戶資訊區塊
        echo '<div class="angminde-user-info">';
        echo '<div class="angminde-user-details">';
        echo '<div class="angminde-avatar">';
        echo '<img src="' . esc_url($avatar_url) . '" alt="頭像">';
        echo '</div>';
        
        if ($is_admin) {
            echo '<div class="angminde-username admin">' . esc_html($user->display_name) . '</div>';
        } else {
            echo '<a href="' . wc_get_account_endpoint_url('edit-account') . '" class="angminde-username customer">' . esc_html($user->display_name) . '</a>';
        }
        echo '</div>';
        
        // 互動按鈕區塊
        echo '<div class="angminde-actions">';
        
        // 按讚按鈕
        if (is_user_logged_in()) {
            echo '<div class="angminde-like-btn" data-message-id="' . $message->id . '">';
        } else {
            echo '<div class="angminde-like-btn disabled">';
        }
        echo '<img src="' . esc_url($like_icon) . '" alt="喜歡">';
        echo '<span class="like-count">' . $like_count . '</span>';
        echo '</div>';
        
        // 回覆計數
        echo '<div class="angminde-reply-count">';
        echo '<img src="https://angminde.tw/wp-content/uploads/2025/06/34f8b2077d914bac37548a121ad4e87a.svg" alt="回覆">';
        echo '<span>' . $reply_count . '</span>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
        
        // 留言內容
        echo '<div class="angminde-message-content">';
        echo '<p>' . nl2br(esc_html($message->message)) . '</p>';
        echo '</div>';
        
        echo '</div>';
        
        // 回覆區塊
        $replies = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}angminde_messages WHERE parent_id = %d AND status = 'published' ORDER BY created_at ASC",
            $message->id
        ));
        
        if (!empty($replies)) {
            echo '<div class="angminde-replies" style="display: none;">';
            foreach ($replies as $reply) {
                $this->render_reply_message($reply, $level + 1);
            }
            echo '</div>';
        }
        
        // 回覆表單（管理員或留言者可見）
        $current_user_id = get_current_user_id();
        $can_reply = ($current_user_id && (current_user_can('manage_options') || $current_user_id == $message->user_id));
        
        if ($can_reply) {
            $current_user_id = get_current_user_id();
            $current_avatar = $this->get_user_avatar($current_user_id);
            
            echo '<div class="angminde-reply-form" style="display: none;">';
            echo '<div class="angminde-reply-input-wrapper">';
            echo '<img src="' . esc_url($current_avatar) . '" class="angminde-reply-avatar" alt="用戶頭像">';
            echo '<textarea class="angminde-reply-input" placeholder="輸入回覆內容..." maxlength="1000"></textarea>';
            echo '</div>';
            echo '<button type="button" class="angminde-reply-submit" data-parent-id="' . $message->id . '">回覆</button>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * 渲染回覆留言
     */
    private function render_reply_message($reply, $level) {
        $user = get_user_by('id', $reply->user_id);
        $is_admin = user_can($reply->user_id, 'manage_options');
        $avatar_url = $this->get_user_avatar($reply->user_id);
        $like_count = $this->get_like_count($reply->id);
        
        // 判斷奇偶層級來選擇按讚圖示
        $like_icon = ($level % 2 == 0) ? 
            'https://angminde.tw/wp-content/uploads/2025/06/cb893407c3aea97c95c75398721b0847.svg' : 
            'https://angminde.tw/wp-content/uploads/2025/06/d37728ea10963397495efe8946e37ce9.svg';
        
        echo '<div class="angminde-reply-item" data-level="' . $level . '">';
        
        // 用戶資訊
        echo '<div class="angminde-reply-header">';
        echo '<div class="angminde-user-details">';
        echo '<div class="angminde-avatar">';
        echo '<img src="' . esc_url($avatar_url) . '" alt="頭像">';
        echo '</div>';
        
        if ($is_admin) {
            echo '<div class="angminde-username admin">' . esc_html($user->display_name) . '</div>';
        } else {
            echo '<a href="' . wc_get_account_endpoint_url('edit-account') . '" class="angminde-username customer">' . esc_html($user->display_name) . '</a>';
        }
        echo '</div>';
        
        // 按讚按鈕
        echo '<div class="angminde-actions">';
        if (is_user_logged_in()) {
            echo '<div class="angminde-like-btn" data-message-id="' . $reply->id . '">';
        } else {
            echo '<div class="angminde-like-btn disabled">';
        }
        echo '<img src="' . esc_url($like_icon) . '" alt="喜歡">';
        echo '<span class="like-count">' . $like_count . '</span>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        // 回覆內容
        echo '<div class="angminde-reply-content">';
        echo '<p>' . nl2br(esc_html($reply->message)) . '</p>';
        echo '</div>';
        
        echo '</div>';
    }
    
    /**
     * 處理按讚AJAX請求
     */
    public function handle_like_comment() {
        // 驗證nonce
        if (!wp_verify_nonce($_POST['nonce'], 'angminde_nonce')) {
            wp_die('安全驗證失敗');
        }
        
        // 檢查是否登入
        if (!is_user_logged_in()) {
            wp_send_json_error('請先登入');
        }
        
        $message_id = intval($_POST['message_id']);
        $user_id = get_current_user_id();
        
        global $wpdb;
        $likes_table = $wpdb->prefix . 'angminde_message_likes';
        $messages_table = $wpdb->prefix . 'angminde_messages';
        
        // 檢查是否已按讚
        $existing_like = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $likes_table WHERE message_id = %d AND user_id = %d",
            $message_id, $user_id
        ));
        
        if ($existing_like) {
            // 取消按讚
            $wpdb->delete($likes_table, array(
                'message_id' => $message_id,
                'user_id' => $user_id
            ));
            $action = 'unliked';
        } else {
            // 新增按讚
            $wpdb->insert($likes_table, array(
                'message_id' => $message_id,
                'user_id' => $user_id
            ));
            $action = 'liked';
        }
        
        // 更新按讚總數
        $like_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $likes_table WHERE message_id = %d",
            $message_id
        ));
        
        $wpdb->update($messages_table, 
            array('like_count' => $like_count),
            array('id' => $message_id)
        );
        
        wp_send_json_success(array(
            'action' => $action,
            'like_count' => $like_count
        ));
    }
    /**
     * 處理新增留言AJAX請求
     */
    public function handle_add_comment() {
        try {
            // 驗證nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'angminde_nonce')) {
                throw new Exception('安全驗證失敗');
            }
            
            // 檢查是否登入
            if (!is_user_logged_in()) {
                throw new Exception('請先登入');
            }
            
            if (!isset($_POST['message'])) {
                throw new Exception('留言內容不能為空');
            }
            
            $message = sanitize_textarea_field($_POST['message']);
            $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
            $user_id = get_current_user_id();
            
            // 驗證內容
            if (empty(trim($message))) {
                throw new Exception('留言內容不能為空');
            }
            
            global $wpdb;
            
            // 如果是回覆，檢查是否為管理員或留言者
            if ($parent_id > 0) {
                $parent_message = $wpdb->get_row($wpdb->prepare(
                    "SELECT user_id FROM {$wpdb->prefix}angminde_messages WHERE id = %d",
                    $parent_id
                ));
                
                if (!$parent_message) {
                    throw new Exception('找不到原始留言');
                }
                
                $is_admin = current_user_can('manage_options');
                $is_author = ($user_id == $parent_message->user_id);
                
                if (!$is_admin && !$is_author) {
                    throw new Exception('只有管理員或留言者可以回覆');
                }
            }
            
            $table_name = $wpdb->prefix . 'angminde_messages';
            
            $result = $wpdb->insert($table_name, array(
                'user_id' => $user_id,
                'parent_id' => $parent_id,
                'message' => $message,
                'created_at' => current_time('mysql'),
                'status' => 'published'
            ));
            
            if ($result === false) {
                error_log('Database Error: ' . $wpdb->last_error);
                throw new Exception('留言發送失敗，請稍後再試');
            }
            
            wp_send_json_success(array(
                'message' => '留言發送成功',
                'reload' => true
            ));
            
        } catch (Exception $e) {
            status_header(400);
            wp_send_json_error($e->getMessage());
        }
    }
    
    /**
     * 獲取用戶頭像
     */
    private function get_user_avatar($user_id) {
        // 直接使用WordPress預設頭像或指定的預設圖片
        $avatar_url = get_avatar_url($user_id);
        return $avatar_url ? $avatar_url : 'https://angminde.tw/wp-content/uploads/2025/06/member-e1749126026306.png';
    }
    
    /**
     * 獲取按讚數量
     */
    private function get_like_count($message_id) {
        global $wpdb;
        $likes_table = $wpdb->prefix . 'angminde_message_likes';
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $likes_table WHERE message_id = %d",
            $message_id
        ));
    }
    
    /**
     * 檢查用戶是否已按讚
     */
    private function user_has_liked($message_id) {
        if (!is_user_logged_in()) {
            return false;
        }
        
        global $wpdb;
        $likes_table = $wpdb->prefix . 'angminde_message_likes';
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $likes_table WHERE message_id = %d AND user_id = %d",
            $message_id, get_current_user_id()
        )) > 0;
    }
    
    /**
     * 獲取回覆數量
     */
    private function get_reply_count($message_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'angminde_messages';
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE parent_id = %d AND status = 'published'",
            $message_id
        ));
    }
}

// 初始化插件
new AngmindeMessageBoard();
?>
