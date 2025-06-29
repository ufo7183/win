<?php
/**
 * 自訂會員彈窗 Shortcode (架構重整最終版)
 *
 * 版本: 2.0
 * 功能:
 * 1. 產生一個觸發按鈕。
 * 2. 根據登入狀態，點擊按鈕後跳出不同的彈窗：
 *    - 未登入：顯示登入/註冊表單。
 *    - 已登入：顯示使用者資訊面板 (控制台、編輯資料、登出)。
 * 3. 使用 AJAX 處理登入和註冊，成功後跳轉回首頁。
 * 4. 與 WooCommerce 使用者系統整合。
 * 5. 支援透過 shortcode 屬性置入 Google 登入外掛。
 *
 * 使用方法:
 * - 基本: [custom_login_popup]
 * - 加上 Google 登入: [custom_login_popup google_login_shortcode='[您的Google登入外掛的shortcode]']
 * - 自訂按鈕圖片: [custom_login_popup trigger_img_url="您的圖片網址"]
 */

if (!class_exists('AMD_Login_Popup_Manager')) {
    class AMD_Login_Popup_Manager {

        private static $is_shortcode_used = false;
        private static $google_login_html = '';

        public static function init() {
            add_shortcode('custom_login_popup', [self::class, 'render_shortcode']);
            add_action('wp_footer', [self::class, 'output_footer_elements']);
            add_action('wp_enqueue_scripts', [self::class, 'register_scripts']);
            add_action('wp_ajax_nopriv_amd_ajax_login', [self::class, 'ajax_login_handler']);
            add_action('wp_ajax_nopriv_amd_ajax_register', [self::class, 'ajax_register_handler']);
        }

        public static function render_shortcode($atts) {
            self::$is_shortcode_used = true;
            $atts = shortcode_atts([
                'google_login_shortcode' => '',
                'trigger_img_url'        => 'https://angminde.tw/wp-content/uploads/2025/06/e466305b533603fbfad9c2e8ffdd4233.svg',
            ], $atts, 'custom_login_popup');

            if (!empty($atts['google_login_shortcode'])) {
                self::$google_login_html = do_shortcode(stripslashes($atts['google_login_shortcode']));
            }

            return '<a href="#" id="amd-popup-trigger" class="amd-popup-trigger-button"><img src="' . esc_url($atts['trigger_img_url']) . '" alt="會員中心"></a>';
        }

        public static function register_scripts() {
            wp_register_script('amd-login-popup-script', false);
            wp_enqueue_script('amd-login-popup-script');

            $current_user = wp_get_current_user();
            $is_logged_in = $current_user->exists();

            wp_localize_script('amd-login-popup-script', 'amd_popup_vars', [
                'ajax_url'          => admin_url('admin-ajax.php'),
                'home_url'          => home_url('/'),
                'is_logged_in'      => $is_logged_in,
                'register_nonce'    => wp_create_nonce('amd-register-nonce'),
                'login_nonce'       => wp_create_nonce('amd-login-nonce'),
                'google_login_html' => self::$google_login_html,
                'user_info'         => $is_logged_in ? [
                    'display_name' => $current_user->display_name,
                    'email'        => $current_user->user_email,
                    'logout_url'   => wp_logout_url(home_url()),
                    'admin_url'    => admin_url(),
                    'profile_url'  => get_edit_user_link($current_user->ID),
                ] : null,
            ]);
        }

        public static function output_footer_elements() {
            if (!self::$is_shortcode_used) return;
            
            // 根據登入狀態決定要顯示哪個彈窗的HTML
            if (is_user_logged_in()) {
                self::render_logged_in_popup();
            } else {
                self::render_logged_out_popup();
            }
            
            self::render_styles();
            self::render_script();
        }

        private static function render_logged_out_popup() {
            ?>
            <div id="amd-login-popup-backdrop" class="amd-popup-backdrop">
                <div class="amd-popup-container">
                    <button id="amd-popup-close" class="amd-popup-close-btn">&times;</button>
                    <div class="amd-popup-tabs">
                        <button class="amd-popup-tab-btn" data-tab="login">登入</button>
                        <button class="amd-popup-tab-btn active" data-tab="register">註冊</button>
                    </div>
                    <div class="amd-popup-content">
                        <div class="amd-popup-message-area"></div>
                        <form id="amd-login-form" class="amd-popup-form" style="display: none;">
                            <div class="amd-form-group"><label for="amd_user_login">使用者帳號或電子郵件</label><input type="text" name="username" id="amd_user_login" required></div>
                            <div class="amd-form-group"><label for="amd_user_pass">密碼</label><input type="password" name="password" id="amd_user_pass" required></div>
                            <button type="submit" class="amd-form-submit-btn">登入</button>
                            <div class="amd-separator"></div><div id="amd-login-google-placeholder" class="amd-google-placeholder"></div>
                        </form>
                        <form id="amd-register-form" class="amd-popup-form">
                            <div class="amd-form-group"><label for="amd_reg_email">電子郵件</label><input type="email" name="email" id="amd_reg_email" required></div>
                            <div class="amd-form-group"><label for="amd_reg_password">密碼</label><input type="password" name="password" id="amd_reg_password" required></div>
                            <div class="amd-form-group amd-privacy-group"><input type="checkbox" name="privacy_policy" id="amd_reg_privacy" value="1" required><label for="amd_reg_privacy">我同意<a href="<?php echo esc_url(get_privacy_policy_url()); ?>" target="_blank">隱私權政策</a></label></div>
                            <button type="submit" class="amd-form-submit-btn">註冊</button>
                            <div class="amd-separator"></div><div id="amd-register-google-placeholder" class="amd-google-placeholder"></div>
                            <p class="amd-back-link-wrapper"><a href="#" class="amd-back-link" data-tab="login">返回登入</a></p>
                        </form>
                    </div>
                </div>
            </div>
            <?php
        }

        private static function render_logged_in_popup() {
            ?>
            <div id="amd-user-menu-backdrop" class="amd-popup-backdrop">
                <div data-layer="state=logout" class="StateLogout" style="padding: 20px 15px; background: var(--black, #222220); border-radius: 10px; justify-content: flex-start; align-items: center; gap: 10px; display: inline-flex; position:relative;">
                    <button id="amd-popup-close" class="amd-popup-close-btn" style="color:white; top:5px; right:10px;">&times;</button>
                    <div data-layer="Frame 84" class="Frame84" style="flex-direction: column; justify-content: flex-start; align-items: center; gap: 20px; display: inline-flex;">
                        <div data-layer="Frame 83" class="Frame83" style="flex-direction: column; justify-content: flex-start; align-items: flex-start; gap: 12px; display: flex;">
                            <div data-layer="Frame 81" class="Frame81" style="justify-content: flex-start; align-items: center; gap: 5px; display: inline-flex;">
                                <div data-svg-wrapper data-layer="Ellipse 1" class="Ellipse1"><svg width="34" height="32" viewBox="0 0 34 32" fill="none" xmlns="http://www.w3.org/2000/svg"><ellipse cx="16.9256" cy="16" rx="16.9256" ry="16" fill="#EBEBEB"/></svg></div>
                                <div data-svg-wrapper data-layer="login" class="Login"><svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.92579 0.333374C8.95451 0.333374 9.94109 0.719682 10.6685 1.40732C11.3959 2.09495 11.8046 3.02758 11.8046 4.00004C11.8046 4.9725 11.3959 5.90513 10.6685 6.59277C9.94109 7.2804 8.95451 7.66671 7.92579 7.66671C6.89707 7.66671 5.91049 7.2804 5.18307 6.59277C4.45566 5.90513 4.047 4.9725 4.047 4.00004C4.047 3.02758 4.45566 2.09495 5.18307 1.40732C5.91049 0.719682 6.89707 0.333374 7.92579 0.333374ZM7.92579 9.50004C12.2118 9.50004 15.6834 11.1409 15.6834 13.1667V15H0.168213V13.1667C0.168213 11.1409 3.63973 9.50004 7.92579 9.50004Z" fill="var(--black, #222220)"/></svg></div>
                                <div data-layer="Frame 80" class="Frame80" style="width: 88.86px; height: 22px; position: relative;">
                                    <div data-layer="username" class="Username" style="left: 0.15px; top: -5px; position: absolute; color: white; font-size: 12px; font-family: Inter; font-weight: 400; line-height: 25px; word-wrap: break-word;">username</div>
                                    <div data-layer="username@gmail.com" class="UsernameGmailCom" style="width: 88.86px; left: 0px; top: 11px; position: absolute; color: white; font-size: 8px; font-family: Inter; font-weight: 400; line-height: 25px; word-wrap: break-word;">username@gmail.com</div>
                                </div>
                            </div>
                            <div data-svg-wrapper data-layer="Vector 1" class="Vector1"><svg width="128" height="2" viewBox="0 0 128 2" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 1H127.5" stroke="white"/></svg></div>
                        </div>
                        <div data-layer="Frame 82" class="Frame82" style="width: 128px; flex-direction: column; justify-content: flex-start; align-items: center; gap: 10px; display: flex; font-family: Inter; font-size: 12px; line-height: 25px;">
                            <a href="#" data-link="admin" style="color:white; text-decoration:none;">控制台</a>
                            <a href="#" data-link="profile" style="color:white; text-decoration:none;">編輯個人資料</a>
                            <a href="#" data-link="logout" style="color:white; text-decoration:underline;">登出</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        private static function render_styles() {
            ?>
            <style>
                :root { --amd-black: #222220; --amd-blue: #3D84F3; --amd-grey1: #EAEAEA; --amd-grey2: #D9D9D9; --amd-grey3: #ACACAC; --amd-white: #FFFFFF; }
                .amd-popup-trigger-button { display: inline-block; } .amd-popup-trigger-button img { max-height: 40px; vertical-align: middle; }
                .amd-popup-backdrop { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.6); z-index: 100000; justify-content: center; align-items: center; }
                .amd-popup-container { background-color: var(--amd-grey1); border-radius: 10px; width: 370px; max-width: 90%; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,0.3); font-family: 'Noto Serif TC', 'Noto Serif', serif; }
                .amd-popup-close-btn { position: absolute; top: 10px; right: 15px; font-size: 28px; font-weight: bold; color: var(--amd-grey3); background: none; border: none; cursor: pointer; line-height: 1; padding: 0; }
                .amd-popup-tabs { display: flex; }
                .amd-popup-tab-btn { flex: 1; height: 56px; display: flex; justify-content: center; align-items: center; background-color: var(--amd-grey2); color: var(--amd-grey3); border: none; cursor: pointer; font-size: 16px; letter-spacing: 3.20px; line-height: 36px; transition: background-color 0.3s, color 0.3s; }
                .amd-popup-tab-btn:first-child { border-top-left-radius: 10px; } .amd-popup-tab-btn:last-child { border-top-right-radius: 10px; }
                .amd-popup-tab-btn.active { background-color: var(--amd-grey1); color: var(--amd-black); }
                .amd-popup-content { padding: 44px; text-align: center; }
                .amd-popup-message-area { display: none; padding: 14px 20px; margin-bottom: 20px; border-radius: 10px; color: var(--amd-black); font-size: 12px; letter-spacing: 1.2px; line-height: 1.5; text-align: left; }
                .amd-popup-message-area.success { background-color: #d4edda; color: #155724; } .amd-popup-message-area.error { background-color: #f8d7da; color: #721c24; }
                .amd-form-group { margin-bottom: 20px; text-align: left; } .amd-form-group label { display: block; margin-bottom: 10px; font-size: 12px; letter-spacing: 1.2px; }
                .amd-form-group input[type="text"], .amd-form-group input[type="email"], .amd-form-group input[type="password"] { width: 100%; height: 48px; padding: 0 20px; background-color: var(--amd-white); border: none; border-radius: 10px; box-sizing: border-box; }
                .amd-privacy-group { display: flex; align-items: center; gap: 10px; } .amd-privacy-group input[type="checkbox"] { width: 20px; height: 20px; flex-shrink: 0; }
                .amd-privacy-group label { margin-bottom: 0; } .amd-privacy-group a { color: var(--amd-blue); text-decoration: none; }
                .amd-form-submit-btn { width: 100%; height: 48px; background-color: var(--amd-black); color: var(--amd-white); border: none; border-radius: 10px; font-size: 20px; font-weight: 600; letter-spacing: 4px; cursor: pointer; transition: background-color 0.3s; }
                .amd-form-submit-btn:disabled { background-color: #6c757d; cursor: not-allowed; }
                .amd-separator { height: 1px; background-color: var(--amd-black); margin: 20px 0; }
                .amd-google-placeholder > div { margin: 10px 0 0 0 !important; }
                .amd-back-link-wrapper { margin-top: 20px; } .amd-back-link { color: var(--amd-black); font-size: 12px; text-decoration: none; letter-spacing: 1.2px; display: inline-flex; align-items: center; gap: 3px; }
                .amd-back-link::before { content: ''; display: inline-block; width: 12px; height: 6px; background-color: var(--amd-black); clip-path: polygon(40% 0, 40% 20%, 100% 20%, 100% 80%, 40% 80%, 40% 100%, 0 50%); transform: rotate(180deg); }
            </style>
            <?php
        }

        private static function render_script() {
            ?>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const trigger = document.getElementById('amd-popup-trigger');
                if (!trigger) return;

                const { is_logged_in, user_info, ajax_url, home_url, login_nonce, register_nonce, google_login_html } = amd_popup_vars;

                const loggedOutBackdrop = document.getElementById('amd-login-popup-backdrop');
                const loggedInBackdrop = document.getElementById('amd-user-menu-backdrop');

                function showPopup(backdrop) { if(backdrop) backdrop.style.display = 'flex'; }
                function hideAllPopups() {
                    if(loggedOutBackdrop) loggedOutBackdrop.style.display = 'none';
                    if(loggedInBackdrop) loggedInBackdrop.style.display = 'none';
                }

                trigger.addEventListener('click', e => {
                    e.preventDefault();
                    if (is_logged_in) {
                        showPopup(loggedInBackdrop);
                    } else {
                        showPopup(loggedOutBackdrop);
                    }
                });

                document.querySelectorAll('.amd-popup-backdrop').forEach(backdrop => {
                    backdrop.addEventListener('click', e => { if (e.target === backdrop) hideAllPopups(); });
                    backdrop.querySelector('#amd-popup-close')?.addEventListener('click', hideAllPopups);
                });

                // --- Logged In Logic ---
                if (is_logged_in && loggedInBackdrop) {
                    loggedInBackdrop.querySelector('.Username').textContent = user_info.display_name;
                    loggedInBackdrop.querySelector('.UsernameGmailCom').textContent = user_info.email;
                    loggedInBackdrop.querySelector('a[data-link="admin"]').href = user_info.admin_url;
                    loggedInBackdrop.querySelector('a[data-link="profile"]').href = user_info.profile_url;
                    loggedInBackdrop.querySelector('a[data-link="logout"]').href = user_info.logout_url;
                }

                // --- Logged Out Logic ---
                if (!is_logged_in && loggedOutBackdrop) {
                    const tabs = loggedOutBackdrop.querySelectorAll('.amd-popup-tab-btn');
                    const loginForm = document.getElementById('amd-login-form');
                    const registerForm = document.getElementById('amd-register-form');
                    const messageArea = loggedOutBackdrop.querySelector('.amd-popup-message-area');
                    const backLinks = loggedOutBackdrop.querySelectorAll('.amd-back-link');

                    function showMessage(type, text) {
                        messageArea.className = 'amd-popup-message-area ' + type;
                        messageArea.innerHTML = text;
                        messageArea.style.display = 'block';
                    }

                    function switchTab(tabName) {
                        tabs.forEach(tab => tab.classList.remove('active'));
                        loggedOutBackdrop.querySelector(`.amd-popup-tab-btn[data-tab="${tabName}"]`).classList.add('active');
                        loginForm.style.display = (tabName === 'login') ? 'block' : 'none';
                        registerForm.style.display = (tabName === 'register') ? 'block' : 'none';
                        messageArea.style.display = 'none';
                    }

                    tabs.forEach(tab => tab.addEventListener('click', () => switchTab(tab.dataset.tab)));
                    backLinks.forEach(link => link.addEventListener('click', e => { e.preventDefault(); switchTab(link.dataset.tab); }));

                    function handleFormSubmit(form, action) {
                        const submitBtn = form.querySelector('button[type="submit"]');
                        const originalBtnText = submitBtn.textContent;
                        submitBtn.textContent = originalBtnText + '中...';
                        submitBtn.disabled = true;

                        const formData = new FormData(form);
                        formData.append('action', action);
                        formData.append('nonce', action === 'amd_ajax_login' ? login_nonce : register_nonce);

                        fetch(ajax_url, { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                showMessage('success', data.message);
                                setTimeout(() => { window.location.href = home_url; }, 1500);
                            } else {
                                showMessage('error', data.message || '發生錯誤，請稍後再試。');
                                submitBtn.textContent = originalBtnText;
                                submitBtn.disabled = false;
                            }
                        })
                        .catch(() => {
                            showMessage('error', '網路連線錯誤，請檢查您的網路。');
                            submitBtn.textContent = originalBtnText;
                            submitBtn.disabled = false;
                        });
                    }

                    loginForm.addEventListener('submit', function(e) { e.preventDefault(); handleFormSubmit(this, 'amd_ajax_login'); });
                    registerForm.addEventListener('submit', function(e) { e.preventDefault(); handleFormSubmit(this, 'amd_ajax_register'); });

                    if (google_login_html) {
                        loggedOutBackdrop.querySelector('#amd-register-google-placeholder').innerHTML = google_login_html;
                        loggedOutBackdrop.querySelector('#amd-login-google-placeholder').innerHTML = google_login_html;
                    }
                    switchTab('register'); // Default to register tab
                }
            });
            </script>
            <?php
        }

        public static function ajax_login_handler() {
            check_ajax_referer('amd-login-nonce', 'nonce');
            $creds = ['user_login' => $_POST['username'], 'user_password' => $_POST['password'], 'remember' => true];
            $user_signon = wp_signon($creds, true);
            if (is_wp_error($user_signon)) {
                wp_send_json(['success' => false, 'message' => '登入失敗：帳號或密碼錯誤。']);
            } else {
                wp_send_json(['success' => true, 'message' => '登入成功！頁面即將跳轉...']);
            }
        }

        public static function ajax_register_handler() {
            check_ajax_referer('amd-register-nonce', 'nonce');
            if (empty($_POST['email']) || empty($_POST['password'])) wp_send_json(['success' => false, 'message' => '電子郵件和密碼為必填欄位。']);
            if (empty($_POST['privacy_policy'])) wp_send_json(['success' => false, 'message' => '您必須同意隱私權政策才能註冊。']);
            if (!is_email($_POST['email'])) wp_send_json(['success' => false, 'message' => '請輸入有效的電子郵件地址。']);
            if (email_exists($_POST['email'])) wp_send_json(['success' => false, 'message' => '這個電子郵件已經被註冊。']);

            $user_id = wp_create_user(sanitize_email($_POST['email']), $_POST['password'], sanitize_email($_POST['email']));

            if (is_wp_error($user_id)) {
                wp_send_json(['success' => false, 'message' => '註冊失敗：' . $user_id->get_error_message()]);
            } else {
                if (class_exists('WooCommerce')) {
                    $user = new WP_User($user_id);
                    $user->set_role('customer');
                }
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);
                wp_send_json(['success' => true, 'message' => '註冊成功！系統已為您自動登入，頁面即將跳轉...']);
            }
        }
    }
    AMD_Login_Popup_Manager::init();
}
?>