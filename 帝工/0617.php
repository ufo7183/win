<?php
/*
 * Plugin Name: Newtide 帝公 自訂客製化功能插件
 * Plugin URI: https://www.newtide.com.tw/
 * Description: 整合前端購物車欄位與後台訂單顯示功能的完整版插件（含日誌檢視器）。
 * Version: 14.0 - Multi-Hook Storage
 * Author: CGLandmark Studio
 * Text Domain: newtide-plugin
   Domain Path: /languages
 */

if (!defined('NEWTIDE_LOG_OPTION_V13')) {
    define('NEWTIDE_LOG_OPTION_V13', 'newtide_debug_logs_v13');
}

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'plugins_loaded', 'newtide_final_plugin_init_v13' );

function newtide_debug_log_v13($message, $data = null) {
    if (defined('NEWTIDE_DEBUG_V13') && NEWTIDE_DEBUG_V13) {
        $logs = get_option(NEWTIDE_LOG_OPTION_V13, array());
        // 限制日誌數量，避免資料庫過大（保留最新 500 筆）
        if (count($logs) > 500) {
            $logs = array_slice($logs, -450);
        }
        $log_entry = array(
            'time' => current_time('mysql'),
            'message' => $message,
            'data' => $data
        );
        $logs[] = $log_entry;
        update_option(NEWTIDE_LOG_OPTION_V13, $logs);
        // 同時寫入錯誤日誌（如果有權限的話）
        $log_message = '[' . date('Y-m-d H:i:s.v') . '] - v13 ' . $message;
        if ($data !== null) {
            $log_message .= ' | Data: ' . print_r($data, true);
        }
        error_log($log_message);
    }
}

function newtide_final_plugin_init_v13() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }
    
    // 定義常數
    define('NEWTIDE_META_KEY_V13', '_estimate_purchasing_schedule');
    define('NEWTIDE_LABEL_V13', 'Estimate Purchasing Schedule');
    define('NEWTIDE_DEBUG_V13', true); // 開啟調試模式
    
    // 記錄日誌
    newtide_debug_log_v13('插件初始化成功 (Plugin Initialized)');
    
    // 註冊 Polylang 字串
    if (function_exists('pll_register_string')) {
        // 購物車相關字串
        pll_register_string('estimate_schedule_label', NEWTIDE_LABEL_V13, 'Newtide Plugin');
        pll_register_string('estimate_schedule_short_term', 'Short Term', 'Newtide Plugin');
        pll_register_string('estimate_schedule_mid_term', 'Mid Term', 'Newtide Plugin');
        pll_register_string('estimate_schedule_long_term', 'Long Term', 'Newtide Plugin');
        pll_register_string('estimate_schedule_1_month', '< 1 month', 'Newtide Plugin');
        pll_register_string('estimate_schedule_1_3_months', '1–3 months', 'Newtide Plugin');
        pll_register_string('estimate_schedule_3_6_months', '3–6 months', 'Newtide Plugin');
        pll_register_string('estimate_schedule_6_12_months', '6–12 months', 'Newtide Plugin');
        pll_register_string('estimate_schedule_1_year', '> 1 year', 'Newtide Plugin');
        
        // 日誌頁面相關字串
        pll_register_string('logs_page_title', 'Newtide 調試日誌', 'Newtide Plugin');
        pll_register_string('logs_page_menu_title', 'Newtide 日誌', 'Newtide Plugin');
        pll_register_string('logs_clear_button', '清除所有日誌', 'Newtide Plugin');
        pll_register_string('logs_refresh_button', '重新整理', 'Newtide Plugin');
        pll_register_string('logs_diagnostic_button', '執行診斷', 'Newtide Plugin');
        pll_register_string('logs_diagnostic_result', '診斷結果', 'Newtide Plugin');
        pll_register_string('logs_no_logs', '目前沒有日誌資料。', 'Newtide Plugin');
        pll_register_string('logs_time_header', '時間', 'Newtide Plugin');
        pll_register_string('logs_message_header', '訊息', 'Newtide Plugin');
        pll_register_string('logs_data_header', '資料', 'Newtide Plugin');
        pll_register_string('logs_count_text', '顯示 %d 筆日誌（最多保留 500 筆）', 'Newtide Plugin');
        
        // 表單相關字串
        pll_register_string('form_subscribe_label', 'Please mail me detail information of products, services and news of events.', 'Newtide Plugin');
        pll_register_string('form_company_label', 'Company', 'Newtide Plugin');
        pll_register_string('form_company_required', 'Company*', 'Newtide Plugin');
        pll_register_string('form_url_label', 'URL', 'Newtide Plugin');
        pll_register_string('form_industry_label', 'Industry', 'Newtide Plugin');
        pll_register_string('form_industry_placeholder', 'Industry', 'Newtide Plugin');
        pll_register_string('form_audio', 'Audio', 'Newtide Plugin');
        pll_register_string('form_video', 'Video', 'Newtide Plugin');
        pll_register_string('form_communication', 'Communication', 'Newtide Plugin');
        pll_register_string('form_computer', 'Computer', 'Newtide Plugin');
        pll_register_string('form_medical', 'Medical', 'Newtide Plugin');
        pll_register_string('form_electronic', 'Electronic', 'Newtide Plugin');
        pll_register_string('form_transportation', 'Transportation', 'Newtide Plugin');
        pll_register_string('form_instrument', 'Instrument', 'Newtide Plugin');
        pll_register_string('form_manufacturing', 'Manufacturing', 'Newtide Plugin');
        pll_register_string('form_retail', 'Retail', 'Newtide Plugin');
        pll_register_string('form_healthcare', 'Healthcare', 'Newtide Plugin');
        pll_register_string('form_automotive', 'Automotive', 'Newtide Plugin');
        pll_register_string('form_aerospace', 'Aerospace', 'Newtide Plugin');
        pll_register_string('form_energy', 'Energy', 'Newtide Plugin');
        pll_register_string('form_construction', 'Construction', 'Newtide Plugin');
        pll_register_string('form_education', 'Education', 'Newtide Plugin');
        pll_register_string('form_finance', 'Finance', 'Newtide Plugin');
        pll_register_string('form_hospitality', 'Hospitality', 'Newtide Plugin');
        pll_register_string('form_food_beverage', 'Food and Beverage', 'Newtide Plugin');
        pll_register_string('form_pharmaceutical', 'Pharmaceutical', 'Newtide Plugin');
        
        // 表單標籤和按鈕
        pll_register_string('form_section_intro', 'To provide you the best service, Please fill out the form below.', 'Newtide Plugin');
        pll_register_string('form_contact_info', 'Information of Contact Person', 'Newtide Plugin');
        pll_register_string('form_company_info', 'Company Information', 'Newtide Plugin');
        pll_register_string('form_others', 'Others', 'Newtide Plugin');
        pll_register_string('form_submit', 'SUBMIT', 'Newtide Plugin');
        
        // 表單欄位標籤
        pll_register_string('form_name_label', 'Name', 'Newtide Plugin');
        pll_register_string('form_last_name_label', 'Last name', 'Newtide Plugin');
        pll_register_string('form_gender_label', 'Gender', 'Newtide Plugin');
        pll_register_string('form_email_label', 'Email', 'Newtide Plugin');
        pll_register_string('form_department_label', 'Department', 'Newtide Plugin');
        pll_register_string('form_job_title_label', 'Job Title', 'Newtide Plugin');
        pll_register_string('form_main_product_label', 'Main Product', 'Newtide Plugin');
        pll_register_string('form_address_label', 'Address', 'Newtide Plugin');
        pll_register_string('form_state_label', 'State', 'Newtide Plugin');
        pll_register_string('form_postcode_label', 'Postal Code', 'Newtide Plugin');
        pll_register_string('form_country_label', 'Country', 'Newtide Plugin');
        pll_register_string('form_phone_label', 'Telephone', 'Newtide Plugin');
        pll_register_string('form_fax_label', 'Fax', 'Newtide Plugin');
        pll_register_string('form_business_type_label', 'Business Type', 'Newtide Plugin');
        pll_register_string('form_request_details_label', 'Request of detail information', 'Newtide Plugin');
        pll_register_string('form_special_comment_label', 'Special Comment', 'Newtide Plugin');
        
        // 表單欄位提示
        pll_register_string('form_name_placeholder', 'Name*', 'Newtide Plugin');
        pll_register_string('form_last_name_placeholder', 'Last name*', 'Newtide Plugin');
        pll_register_string('form_email_placeholder', 'E-mail*', 'Newtide Plugin');
        pll_register_string('form_department_placeholder', 'Department*', 'Newtide Plugin');
        pll_register_string('form_job_title_placeholder', 'Job Title*', 'Newtide Plugin');
        pll_register_string('form_main_product_placeholder', 'Main Product', 'Newtide Plugin');
        pll_register_string('form_address_placeholder', 'Address', 'Newtide Plugin');
        pll_register_string('form_state_placeholder', 'State', 'Newtide Plugin');
        pll_register_string('form_postcode_placeholder', 'Postal Code', 'Newtide Plugin');
        pll_register_string('form_phone_placeholder', 'Telephone*', 'Newtide Plugin');
        pll_register_string('form_fax_placeholder', 'Fax*', 'Newtide Plugin');
        pll_register_string('form_special_comment_placeholder', 'Special Comment', 'Newtide Plugin');
        
        // 表單選項
        pll_register_string('form_gender_placeholder', 'Gender', 'Newtide Plugin');
        pll_register_string('form_gender_male', 'Male', 'Newtide Plugin');
        pll_register_string('form_gender_female', 'Female', 'Newtide Plugin');
        
        // 表單驗證提示
        pll_register_string('form_required', '*', 'Newtide Plugin');
        
        // 表單細節請求選項
        pll_register_string('form_fob_prices', 'FOB prices (for minimum order quantity)', 'Newtide Plugin');
        pll_register_string('form_min_order_qty', 'Minimum order quantity', 'Newtide Plugin');
        pll_register_string('form_lead_time', 'Lead Time', 'Newtide Plugin');
        pll_register_string('form_shipping_date', 'Shipping Date', 'Newtide Plugin');
        pll_register_string('form_delivery_date', 'Expected Delivery Date', 'Newtide Plugin');
        pll_register_string('form_sample_info', 'Sample availability/Cost', 'Newtide Plugin');
        pll_register_string('form_company_brochure', 'Company Brochure', 'Newtide Plugin');
        pll_register_string('form_international_standards', 'International standards met', 'Newtide Plugin');
        pll_register_string('form_others_info', 'Others', 'Newtide Plugin');
        
        // 表單業務類型選項
        pll_register_string('form_business_importer', 'Importer', 'Newtide Plugin');
        pll_register_string('form_business_distributor', 'Distributor', 'Newtide Plugin');
        pll_register_string('form_business_exporter', 'Exporter', 'Newtide Plugin');
        pll_register_string('form_business_trader', 'Trader', 'Newtide Plugin');
        pll_register_string('form_business_wholesaler', 'Wholesaler', 'Newtide Plugin');
        pll_register_string('form_business_retailer', 'Retailer', 'Newtide Plugin');
        pll_register_string('form_business_agent', 'Agent', 'Newtide Plugin');
        pll_register_string('form_business_manufacturer', 'Manufacturer', 'Newtide Plugin');
        pll_register_string('form_business_trading_company', 'Trading Company', 'Newtide Plugin');
        pll_register_string('form_business_others', 'Others', 'Newtide Plugin');

        // 國家名稱
        pll_register_string('form_country_taiwan', 'Taiwan', 'Newtide Plugin');
        pll_register_string('form_country_us', 'United States', 'Newtide Plugin');
        pll_register_string('form_country_canada', 'Canada', 'Newtide Plugin');
        pll_register_string('form_country_uk', 'United Kingdom', 'Newtide Plugin');
        pll_register_string('form_country_australia', 'Australia', 'Newtide Plugin');
        pll_register_string('form_country_germany', 'Germany', 'Newtide Plugin');
        pll_register_string('form_country_france', 'France', 'Newtide Plugin');
        pll_register_string('form_country_japan', 'Japan', 'Newtide Plugin');
        pll_register_string('form_country_china', 'China', 'Newtide Plugin');
        pll_register_string('form_country_india', 'India', 'Newtide Plugin');
        
        newtide_debug_log_v13('Polylang 字串已註冊');
    }
    
    // 確保所有函數都已經定義
    if (!function_exists('newtide_get_schedule_from_session_v13')) {
        newtide_debug_log_v13('函數未定義 - 創建函數');
        function newtide_get_schedule_from_session_v13($cart_item, $values) {
            if (isset($values[NEWTIDE_META_KEY_V13])) {
                $cart_item[NEWTIDE_META_KEY_V13] = $values[NEWTIDE_META_KEY_V13];
                newtide_debug_log_v13('從 Session 載入資料', array(
                    'product_id' => $cart_item['product_id'],
                    'value' => $values[NEWTIDE_META_KEY_V13]
                ));
            }
            return $cart_item;
        }
    }

    // 添加短碼功能
    add_shortcode('newtide_cart_form', 'newtide_display_cart_form_v13');
    
    function newtide_display_cart_form_v13() {
        ob_start();
        ?>
        <div class="newtide-custom-cart">
            <?php woocommerce_cart(); ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // 註冊所有 hooks
    add_action('admin_menu', 'newtide_add_admin_menu_v13');
    
    // 前端相關
    add_action('woocommerce_after_cart_item_name', 'newtide_add_schedule_field_to_cart_v13', 10, 2);
    add_filter('woocommerce_get_cart_item_from_session', 'newtide_get_schedule_from_session_v13', 20, 2);
    add_action('wp_footer', 'newtide_add_custom_ajax_script_v13');
    
    // 定義 AJAX 腳本函數
    if (!function_exists('newtide_add_custom_ajax_script_v13')) {
        function newtide_add_custom_ajax_script_v13() {
            if (!is_cart()) return;
            $nonce = wp_create_nonce('newtide-nonce');
            ?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // 設置 AJAX 參數
                window.newtide_ajax = {
                    ajaxurl: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                    nonce: '<?php echo esc_attr($nonce); ?>'
                };

                // 購物車預估購買時間更新
                $(document).on('change', '.estimate-purchase-select-field', function() {
                    var $this = $(this);
                    var itemKey = $this.data('item-key');
                    var value = $this.val();

                    $.ajax({
                        url: newtide_ajax.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'newtide_update_schedule_v13',
                            nonce: newtide_ajax.nonce,
                            item_key: itemKey,
                            value: value
                        },
                        success: function(response) {
                            if (response.success) {
                                console.log('預估購買時間已更新');
                            } else {
                                console.error('更新預估購買時間失敗');
                            }
                        }
                    });
                });

                // 購物車數量更新
                $(document).on('change', '.quantity-input', function() {
                    var $this = $(this);
                    var itemKey = $this.data('cart-item-key');
                    var quantity = $this.val();

                    $.ajax({
                        url: newtide_ajax.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'newtide_update_quantity',
                            nonce: newtide_ajax.nonce,
                            cart_item_key: itemKey,
                            quantity: quantity
                        },
                        success: function(response) {
                            if (response.success) {
                                console.log('數量已更新');
                                $(document.body).trigger('wc_update_cart');
                            } else {
                                console.error('更新數量失敗');
                            }
                        }
                    });
                });

                // 刪除購物車項目
                $(document).on('click', '.remove-item', function(e) {
                    e.preventDefault();
                    var $this = $(this);
                    var itemKey = $this.data('cart-item-key');

                    if (confirm('確定要刪除這個項目嗎？')) {
                        $.ajax({
                            url: newtide_ajax.ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'newtide_remove_item',
                                nonce: newtide_ajax.nonce,
                                cart_item_key: itemKey
                            },
                            success: function(response) {
                                if (response.success) {
                                    console.log('項目已刪除');
                                    $(document.body).trigger('wc_update_cart');
                                } else {
                                    console.error('刪除項目失敗');
                                }
                            }
                        });
                    }
                });

                // 購物車更新後重新綁定事件
                $(document.body).on('updated_cart_totals', function() {
                    // 重新綁定數量更新事件
                    $('.quantity-input').off('change').on('change', function() {
                        var $this = $(this);
                        var itemKey = $this.data('cart-item-key');
                        var quantity = $this.val();

                        $.ajax({
                            url: newtide_ajax.ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'newtide_update_quantity',
                                nonce: newtide_ajax.nonce,
                                cart_item_key: itemKey,
                                quantity: quantity
                            },
                            success: function(response) {
                                if (response.success) {
                                    console.log('數量已更新');
                                } else {
                                    console.error('更新數量失敗');
                                }
                            }
                        });
                    });

                    // 重新綁定刪除事件
                    $('.remove-item').off('click').on('click', function(e) {
                        e.preventDefault();
                        var $this = $(this);
                        var itemKey = $this.data('cart-item-key');

                        if (confirm('確定要刪除這個項目嗎？')) {
                            $.ajax({
                                url: newtide_ajax.ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'newtide_remove_item',
                                    nonce: newtide_ajax.nonce,
                                    cart_item_key: itemKey
                                },
                                success: function(response) {
                                    if (response.success) {
                                        console.log('項目已刪除');
                                        $(document.body).trigger('wc_update_cart');
                                    } else {
                                        console.error('刪除項目失敗');
                                    }
                                }
                            });
                        }
                    });
                });
            });
            </script>
            <?php
        }
    }
    
    // AJAX 相關
    add_action('wp_ajax_newtide_update_schedule_v13', 'newtide_ajax_update_schedule_handler_v13');
    add_action('wp_ajax_nopriv_newtide_update_schedule_v13', 'newtide_ajax_update_schedule_handler_v13');
    add_action('wp_ajax_newtide_clear_logs_v13', 'newtide_ajax_clear_logs_v13');
    add_action('wp_ajax_newtide_run_diagnostic_v13', 'newtide_ajax_run_diagnostic_v13');
    
    // 短碼功能
    add_shortcode('newtide_custom_cart', 'newtide_custom_cart_shortcode_v13');
    
    // 短碼處理函數
    function newtide_custom_cart_shortcode_v13($atts) {
        if (!class_exists('WooCommerce')) {
            return '<p>WooCommerce 未啟用。</p>';
        }
        
        ob_start();
        ?>
        <div class="newtide-custom-cart">
            <?php woocommerce_cart(); ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // AJAX 處理函數 - 更新購物車數量
    add_action('wp_ajax_newtide_update_quantity', 'newtide_ajax_update_quantity');
    add_action('wp_ajax_nopriv_newtide_update_quantity', 'newtide_ajax_update_quantity');
    
    function newtide_ajax_update_quantity() {
        check_ajax_referer('newtide-nonce', 'nonce');
        
        if (!isset($_POST['cart_item_key']) || !isset($_POST['quantity'])) {
            wp_send_json_error('Missing parameters');
        }
        
        $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
        $quantity = intval($_POST['quantity']);
        
        if ($quantity < 0) {
            wp_send_json_error('Invalid quantity');
        }
        
        WC()->cart->set_quantity($cart_item_key, $quantity, false);
        wp_send_json_success(array('message' => 'Quantity updated successfully'));
    }
    
    // AJAX 處理函數 - 刪除購物車項目
    add_action('wp_ajax_newtide_remove_item', 'newtide_ajax_remove_item');
    add_action('wp_ajax_nopriv_newtide_remove_item', 'newtide_ajax_remove_item');
    
    function newtide_ajax_remove_item() {
        check_ajax_referer('newtide-nonce', 'nonce');
        
        if (!isset($_POST['cart_item_key'])) {
            wp_send_json_error('Missing cart item key');
        }
        
        $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
        WC()->cart->remove_cart_item($cart_item_key);
        wp_send_json_success(array('message' => 'Item removed successfully'));
    }
    
    // 儲存邏輯
    add_action( 'woocommerce_checkout_create_order_line_item', 'newtide_add_custom_data_to_order_item_v13', 10, 4 );
    add_action( 'woocommerce_new_order_item', 'newtide_save_order_item_meta_v13', 10, 3 );
    add_action( 'woocommerce_add_order_item_meta', 'newtide_legacy_save_order_item_meta_v13', 10, 3 );
    add_filter( 'woocommerce_hidden_order_itemmeta', 'newtide_hide_order_item_meta_v13' );
    
    // 後台與前端顯示
    add_action('woocommerce_before_order_itemmeta', 'newtide_display_admin_order_item_field_v13', 10, 2);
    add_action('woocommerce_saved_order_items', 'newtide_save_admin_order_item_field_v13', 10, 2);
    add_action('woocommerce_order_item_meta_end', 'newtide_display_schedule_in_order_details_v13', 10, 4);
    
    // 調試相關
    // add_action('woocommerce_after_order_itemmeta', 'newtide_debug_display_all_meta_v13', 10, 2);
    
    // 確保所有 hooks 都已經註冊
    newtide_debug_log_v13('所有 hooks 已註冊');
}

function newtide_get_schedule_from_session_v13($cart_item, $values) {
    if (isset($values[NEWTIDE_META_KEY_V13])) {
        $cart_item[NEWTIDE_META_KEY_V13] = $values[NEWTIDE_META_KEY_V13];
        newtide_debug_log_v13('從 Session 載入資料', array(
            'product_id' => $cart_item['product_id'],
            'value' => $values[NEWTIDE_META_KEY_V13]
        ));
    }
    return $cart_item;
}

// --- 新增管理員選單 ---
function newtide_add_admin_menu_v13() {
    add_menu_page(
        'Newtide 調試日誌',
        'Newtide 日誌',
        'manage_options',
        'newtide-debug-logs',
        'newtide_debug_logs_page_v13',
        'dashicons-clipboard',
        58
    );
}

// --- 日誌頁面內容 ---
function newtide_debug_logs_page_v13() {
    ?>
    <div class="wrap">
        <h1><?php echo pll__('Newtide 調試日誌', 'logs_page_title'); ?></h1>
        
        <div style="margin: 20px 0;">
            <button id="newtide-clear-logs" class="button button-secondary"><?php echo pll__('清除所有日誌', 'logs_clear_button'); ?></button>
            <button id="newtide-refresh-logs" class="button button-primary"><?php echo pll__('重新整理', 'logs_refresh_button'); ?></button>
            <button id="newtide-run-diagnostic" class="button button-secondary"><?php echo pll__('執行診斷', 'logs_diagnostic_button'); ?></button>
            <span id="newtide-log-status" style="margin-left: 20px;"></span>
        </div>
        
        <div id="newtide-diagnostic-result" style="display: none; margin: 20px 0; padding: 15px; background: #fff; border: 1px solid #ccc;">
            <h3><?php echo pll__('診斷結果', 'logs_diagnostic_result'); ?></h3>
            <div id="diagnostic-content"></div>
        </div>
        
        <?php
        $logs = get_option(NEWTIDE_LOG_OPTION_V13, array());
        $logs = array_reverse($logs); // 最新的在上面
        
        if (empty($logs)) {
            echo '<p>' . pll__('目前沒有日誌資料。', 'logs_no_logs') . '</p>';
        } else {
            ?>
            <div style="background: #f9f9f9; border: 1px solid #ddd; padding: 10px; max-height: 600px; overflow-y: auto;">
                <table class="widefat" style="background: white;">
                    <thead>
                        <tr>
                            <th style="width: 180px;"><?php echo pll__('時間', 'logs_time_header'); ?></th>
                            <th><?php echo pll__('訊息', 'logs_message_header'); ?></th>
                            <th><?php echo pll__('資料', 'logs_data_header'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $index => $log): ?>
                            <tr class="<?php echo $index % 2 ? 'alternate' : ''; ?>">
                                <td style="font-family: monospace; font-size: 12px;">
                                    <?php echo esc_html($log['time']); ?>
                                </td>
                                <td>
                                    <?php echo esc_html($log['message']); ?>
                                </td>
                                <td>
                                    <?php 
                                    if ($log['data'] !== null) {
                                        echo '<pre style="margin: 0; font-size: 11px; max-width: 500px; overflow-x: auto;">';
                                        echo esc_html(print_r($log['data'], true));
                                        echo '</pre>';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p style="margin-top: 10px; color: #666;">
                <?php echo sprintf(pll__('顯示 %d 筆日誌（最多保留 500 筆）', 'logs_count_text'), count($logs)); ?>
            </p>
            <?php
        }
        ?>
        
        <script type="text/javascript">
        jQuery(function($) {
            $('#newtide-clear-logs').on('click', function() {
                if (confirm('確定要清除所有日誌嗎？')) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'newtide_clear_logs_v13',
                            security: '<?php echo wp_create_nonce('newtide-clear-logs-nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#newtide-log-status').html('<span style="color: green;">日誌已清除！</span>');
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            }
                        }
                    });
                }
            });
            
            $('#newtide-refresh-logs').on('click', function() {
                location.reload();
            });
            
            $('#newtide-run-diagnostic').on('click', function() {
                $('#diagnostic-content').html('執行診斷中...');
                $('#newtide-diagnostic-result').show();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'newtide_run_diagnostic_v13',
                        security: '<?php echo wp_create_nonce('newtide-diagnostic-nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#diagnostic-content').html(response.data);
                        } else {
                            $('#diagnostic-content').html('<p style="color: red;">診斷失敗</p>');
                        }
                    }
                });
            });
        });
        </script>
</div>

// --- AJAX 清除日誌 ---
function newtide_ajax_clear_logs_v13() {
    check_ajax_referer('newtide-clear-logs-nonce', 'security');
    
    if (current_user_can('manage_options')) {
        delete_option(NEWTIDE_LOG_OPTION_V13);
        newtide_debug_log_v13('日誌已被清除');
        wp_send_json_success('Logs cleared');
    } else {
        wp_send_json_error('Permission denied');
    }
}

// --- 前端 AJAX 相關函式 ---
function newtide_add_schedule_field_to_cart_v13($cart_item, $cart_item_key) {
    $value = isset($cart_item[NEWTIDE_META_KEY_V13]) ? $cart_item[NEWTIDE_META_KEY_V13] : '';
    newtide_debug_log_v13('顯示購物車欄位', array('cart_item_key' => $cart_item_key, 'current_value' => $value));
    
   echo '<div class="estimate-purchase-schedule" style="margin-top: 5px;">';
echo '<select data-item-key="' . esc_attr($cart_item_key) . '" class="estimate-purchase-select-field" style="width: 12em;">';

// 第一個空白選項標題
echo '<option value=""' . selected($value, '', false) . '>' . esc_html(pll__('Estimate Purchasing Schedule', 'estimate_schedule_label')) . '</option>';

// 第一組 optgroup：Short Term
echo '<optgroup label="' . esc_attr(pll__('Short Term', 'estimate_schedule_short_term')) . '">';
echo '<option value="< 1 month"' . selected($value, '< 1 month', false) . '>' . esc_html(pll__('< 1 month', 'estimate_schedule_1_month')) . '</option>';
echo '<option value="1-3 months"' . selected($value, '1-3 months', false) . '>' . esc_html(pll__('1–3 months', 'estimate_schedule_1_3_months')) . '</option>';
echo '</optgroup>';

// 第二組 optgroup：Mid Term
echo '<optgroup label="' . esc_attr(pll__('Mid Term', 'estimate_schedule_mid_term')) . '">';
echo '<option value="3-6 months"' . selected($value, '3-6 months', false) . '>' . esc_html(pll__('3–6 months', 'estimate_schedule_3_6_months')) . '</option>';
echo '<option value="6-12 months"' . selected($value, '6-12 months', false) . '>' . esc_html(pll__('6–12 months', 'estimate_schedule_6_12_months')) . '</option>';
echo '</optgroup>';

// 第三組 optgroup：Long Term
echo '<optgroup label="' . esc_attr(pll__('Long Term', 'estimate_schedule_long_term')) . '">';
echo '<option value="> 1 year"' . selected($value, '> 1 year', false) . '>' . esc_html(pll__('> 1 year', 'estimate_schedule_1_year')) . '</option>';
echo '</optgroup>';

echo '</select>';
echo '</div>';

}

function newtide_add_custom_ajax_script_v13() {
    if (!is_cart()) return;
    $nonce = wp_create_nonce('newtide-schedule-nonce-v13');
    ?>
    <script type="text/javascript">
    jQuery(function($) {
        $(document).on('change', '.estimate-purchase-select-field', function() {
            var selectField = $(this);
            $('div.woocommerce').block({ message: null, overlayCSS: { background: '#fff', opacity: 0.6 } });
            $.ajax({
                type: 'POST',
                url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                data: {
                    action: 'newtide_update_schedule_v13',
                    security: '<?php echo $nonce; ?>',
                    cart_item_key: selectField.data('item-key'),
                    schedule_value: selectField.val()
                },
                success: function(response) {
                    console.log('AJAX Response:', response);
                    $(document.body).trigger('wc_update_cart');
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    $('div.woocommerce').unblock();
                }
            });
        });
        $(document.body).on('updated_wc_div', function(){
            $('div.woocommerce').unblock();
        });
    });
    </script>
 
}

function newtide_ajax_update_schedule_handler_v13() {
    check_ajax_referer('newtide-schedule-nonce-v13', 'security');
    
    if (!isset($_POST['cart_item_key'])) {
        newtide_debug_log_v13('AJAX Error: Missing cart_item_key');
        wp_send_json_error('Missing key.');
    }
    
    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
    $schedule_value = isset($_POST['schedule_value']) ? sanitize_text_field($_POST['schedule_value']) : '';
    
    newtide_debug_log_v13('AJAX Handler 啟動', array(
        'cart_item_key' => $cart_item_key,
        'schedule_value' => $schedule_value
    ));
    
    $cart = WC()->cart->get_cart();
    
    if (isset($cart[$cart_item_key])) {
        $cart[$cart_item_key][NEWTIDE_META_KEY_V13] = $schedule_value;
        WC()->cart->set_cart_contents($cart);
        WC()->cart->set_session();
        
        newtide_debug_log_v13('資料已寫入購物車 Session', array(
            'cart_item_key' => $cart_item_key,
            'schedule_value' => $schedule_value,
            'product_name' => $cart[$cart_item_key]['data']->get_name()
        ));
        
        wp_send_json_success('Schedule updated.');
    } else {
        newtide_debug_log_v13('AJAX Error: Cart item not found', array('cart_item_key' => $cart_item_key));
        wp_send_json_error('Cart item not found.');
    }
}

// --- 改進的儲存邏輯 ---
function newtide_add_custom_data_to_order_item_v13( $item, $cart_item_key, $values, $order ) {
    newtide_debug_log_v13('Hook: woocommerce_checkout_create_order_line_item 觸發', array(
        'cart_item_key' => $cart_item_key,
        'product_id' => $values['product_id'],
        'product_name' => $item->get_name(),
        'has_schedule' => isset($values[NEWTIDE_META_KEY_V13]),
        'schedule_value' => isset($values[NEWTIDE_META_KEY_V13]) ? $values[NEWTIDE_META_KEY_V13] : 'NOT SET'
    ));
    
    if ( isset( $values[NEWTIDE_META_KEY_V13] ) && ! empty( $values[NEWTIDE_META_KEY_V13] ) ) {
        $schedule_value = $values[NEWTIDE_META_KEY_V13];
        
        // 使用 WooCommerce 的標準方法儲存 meta data
        $item->add_meta_data( NEWTIDE_META_KEY_V13, $schedule_value, true );
        
        newtide_debug_log_v13('訂單項目資料已儲存 (方法1)', array(
            'order_id' => $order->get_id(),
            'item_id' => $item->get_id(),
            'meta_key' => NEWTIDE_META_KEY_V13,
            'meta_value' => $schedule_value
        ));
        
        // 立即儲存以確保資料寫入
        $item->save();
        
        // 驗證儲存
        $saved_value = $item->get_meta(NEWTIDE_META_KEY_V13);
        newtide_debug_log_v13('驗證儲存結果 (方法1)', array(
            'saved_value' => $saved_value,
            'save_success' => ($saved_value === $schedule_value)
        ));
    } else {
        newtide_debug_log_v13('略過儲存 - 沒有排程資料', array(
            'product_name' => $item->get_name()
        ));
    }
}

// --- 備用儲存方法 ---
function newtide_save_order_item_meta_v13( $item_id, $item, $order_id ) {
    newtide_debug_log_v13('Hook: woocommerce_new_order_item 觸發', array(
        'item_id' => $item_id,
        'order_id' => $order_id,
        'item_type' => $item->get_type()
    ));
    
    // 只處理產品項目
    if ( $item->get_type() !== 'line_item' ) {
        return;
    }
    
    // 檢查是否已經有資料
    $existing_value = $item->get_meta(NEWTIDE_META_KEY_V13);
    if ( $existing_value ) {
        newtide_debug_log_v13('訂單項目已有資料，跳過', array(
            'existing_value' => $existing_value
        ));
        return;
    }
    
    // 嘗試從購物車 session 取得資料
    $cart = WC()->cart->get_cart();
    foreach ( $cart as $cart_item_key => $cart_item ) {
        if ( $cart_item['product_id'] == $item->get_product_id() ) {
            if ( isset( $cart_item[NEWTIDE_META_KEY_V13] ) && ! empty( $cart_item[NEWTIDE_META_KEY_V13] ) ) {
                $schedule_value = $cart_item[NEWTIDE_META_KEY_V13];
                
                // 使用 wc_add_order_item_meta 儲存
                wc_add_order_item_meta( $item_id, NEWTIDE_META_KEY_V13, $schedule_value, true );
                
                newtide_debug_log_v13('訂單項目資料已儲存 (方法2)', array(
                    'item_id' => $item_id,
                    'product_id' => $item->get_product_id(),
                    'meta_value' => $schedule_value
                ));
                
                break;
            }
        }
    }
}

// --- 隱藏不必要的 meta 顯示 ---
function newtide_hide_order_item_meta_v13( $hidden_meta ) {
    $hidden_meta[] = NEWTIDE_META_KEY_V13;
    return $hidden_meta;
}

// --- Legacy 儲存方法 (相容舊版) ---
function newtide_legacy_save_order_item_meta_v13( $item_id, $values, $cart_item_key ) {
    newtide_debug_log_v13('Hook: woocommerce_add_order_item_meta 觸發 (Legacy)', array(
        'item_id' => $item_id,
        'cart_item_key' => $cart_item_key,
        'has_schedule' => isset($values[NEWTIDE_META_KEY_V13])
    ));
    
    if ( isset( $values[NEWTIDE_META_KEY_V13] ) && ! empty( $values[NEWTIDE_META_KEY_V13] ) ) {
        $schedule_value = $values[NEWTIDE_META_KEY_V13];
        
        // 使用舊版方法儲存
        wc_add_order_item_meta( $item_id, NEWTIDE_META_KEY_V13, $schedule_value, true );
        
        newtide_debug_log_v13('訂單項目資料已儲存 (Legacy 方法)', array(
            'item_id' => $item_id,
            'meta_value' => $schedule_value
        ));
    }
}


// --- 後台顯示函式 (僅顯示，不可編輯) ---
function newtide_display_admin_order_item_field_v13($item_id, $item) {
    if (!is_a($item, 'WC_Order_Item_Product')) return;

    // 使用正確的 KEY 來讀取資料
    $value = $item->get_meta(NEWTIDE_META_KEY_V13);

    // 如果有值，才顯示
    if ($value) {
        echo '<div style="margin-top: 5px;">';
        echo '<strong>' . esc_html(NEWTIDE_LABEL_V13) . ':</strong> ' . esc_html($value);
        echo '</div>';
    }
}

// --- 調試顯示所有 meta ---
function newtide_debug_display_all_meta_v13($item_id, $item) {
    if (!NEWTIDE_DEBUG_V13 || !is_a($item, 'WC_Order_Item_Product')) return;
    
    $all_meta = $item->get_meta_data();
    if (!empty($all_meta)) {
        echo '<div style="margin-top:10px; padding:10px; background:#f0f0f0; font-size:11px; font-family:monospace;">';
        echo '<strong>DEBUG - All Meta Data:</strong><br>';
        foreach ($all_meta as $meta) {
            echo sprintf('[%s] => %s<br>', 
                esc_html($meta->key), 
                esc_html(is_array($meta->value) ? json_encode($meta->value) : $meta->value)
            );
        }
        echo '</div>';
    }
}

// --- 儲存後台修改 ---
function newtide_save_admin_order_item_field_v13($order_id, $items) {
    if (isset($_POST['newtide_schedule']) && is_array($_POST['newtide_schedule'])) {
        newtide_debug_log_v13('後台儲存訂單項目', array(
            'order_id' => $order_id,
            'posted_data' => $_POST['newtide_schedule']
        ));
        
        foreach ($_POST['newtide_schedule'] as $item_id => $schedule) {
            $sanitized_value = sanitize_text_field($schedule);
            if ($sanitized_value !== '') {
                wc_update_order_item_meta($item_id, NEWTIDE_META_KEY_V13, $sanitized_value);
                newtide_debug_log_v13('更新項目 meta', array(
                    'item_id' => $item_id,
                    'new_value' => $sanitized_value
                ));
            } else {
                wc_delete_order_item_meta($item_id, NEWTIDE_META_KEY_V13);
                newtide_debug_log_v13('刪除項目 meta', array('item_id' => $item_id));
            }
        }
    }
}

// --- 前端訂單詳情顯示 ---
function newtide_display_schedule_in_order_details_v13($item_id, $item, $order, $plain_text) {
    $schedule = $item->get_meta(NEWTIDE_META_KEY_V13);
    
    newtide_debug_log_v13('前端顯示訂單詳情', array(
        'item_id' => $item_id,
        'schedule' => $schedule,
        'is_plain_text' => $plain_text
    ));
    
    if ($schedule && is_scalar($schedule)) {
        $label = '<strong>' . esc_html(NEWTIDE_LABEL_V13) . ':</strong> ';
        if ($plain_text) {
            echo "\n" . strip_tags($label) . ' ' . esc_html($schedule);
        } else {
            echo '<div style="margin-top: 5px; color: #2271b1;">' . $label . esc_html($schedule) . '</div>';
        }
    }
}

// --- 診斷功能 ---
function newtide_ajax_run_diagnostic_v13() {
    check_ajax_referer('newtide-diagnostic-nonce', 'security');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }
    
    $diagnostic = array();
    
    // 檢查 WooCommerce 版本
    $diagnostic[] = '<h4>WooCommerce 資訊</h4>';
    $diagnostic[] = 'WooCommerce 版本: ' . WC_VERSION;
    
    // 檢查 Hooks
    $diagnostic[] = '<h4>Hook 檢查</h4>';
    $hooks_to_check = array(
        'woocommerce_checkout_create_order_line_item',
        'woocommerce_new_order_item',
        'woocommerce_add_order_item_meta'
    );
    
    foreach ($hooks_to_check as $hook) {
        $has_actions = has_action($hook);
        $diagnostic[] = sprintf('%s: %s', $hook, $has_actions ? '✓ 已註冊' : '✗ 未註冊');
    }
    
    // 檢查購物車
    $diagnostic[] = '<h4>購物車狀態</h4>';
    $cart = WC()->cart->get_cart();
    if (empty($cart)) {
        $diagnostic[] = '購物車是空的';
    } else {
        foreach ($cart as $key => $item) {
            $schedule = isset($item[NEWTIDE_META_KEY_V13]) ? $item[NEWTIDE_META_KEY_V13] : '無';
            $diagnostic[] = sprintf('商品: %s | Schedule: %s', 
                $item['data']->get_name(), 
                $schedule
            );
        }
    }
    
    // 檢查最近的訂單
    $diagnostic[] = '<h4>最近訂單檢查</h4>';
    $recent_orders = wc_get_orders(array(
        'limit' => 3,
        'orderby' => 'date',
        'order' => 'DESC'
    ));
    
    foreach ($recent_orders as $order) {
        $diagnostic[] = sprintf('<strong>訂單 #%d</strong>', $order->get_id());
        foreach ($order->get_items() as $item) {
            $schedule = $item->get_meta(NEWTIDE_META_KEY_V13);
            $diagnostic[] = sprintf('- %s: %s', 
                $item->get_name(), 
                $schedule ? $schedule : '無資料'
            );
        }
    }
    
    $html = '<div>' . implode('<br>', $diagnostic) . '</div>';
    
    newtide_debug_log_v13('執行診斷', array('timestamp' => current_time('mysql')));
    
    wp_send_json_success($html);
}



// --- Shortcode for Custom Cart ---
add_shortcode('newtide_custom_cart', 'newtide_custom_cart_shortcode_v13');

// --- 自訂購物車 Shortcode 函式 ---
function newtide_custom_cart_shortcode_v13($atts) {
    // 確保 WooCommerce 已載入
    if (!class_exists('WooCommerce')) {
        return '<p>WooCommerce 未啟用。</p>';
    }
    
    // 開始輸出緩衝
    ob_start();
    
    // 獲取購物車內容
    $cart = WC()->cart->get_cart();
    
    if (empty($cart)) {
        ?>
        <div class="newtide-custom-cart empty-cart">
            <p>您的詢價單目前是空的。</p>
            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="button">繼續選購</a>
        </div>
        <?php
        return ob_get_clean();
    
    
    // 產生 nonce
    $nonce = wp_create_nonce('newtide-custom-cart-nonce');
    ?>
        <form class="newtide-cart-form" method="post">
            <?php wp_nonce_field('newtide-custom-checkout', 'newtide_checkout_nonce'); ?>
            
            <table class="newtide-cart-table">
                <thead>
                    <tr>
                        <th class="product-thumbnail">View Picture</th>
                        <th class="product-name">Selected Items</th>
                        <th class="product-quantity">Estimate Purchasing Quantity</th>
                        <th class="product-schedule">Estimate Purchasing Schedule</th>
                        <th class="product-remove">Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($cart as $cart_item_key => $cart_item) {
                        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                        $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                        
                        if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
                            $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                            $product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
                            $product_thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);
                            $product_price = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
                            
                            // 獲取當前的 schedule 值
                            $schedule_value = isset($cart_item[NEWTIDE_META_KEY_V13]) ? $cart_item[NEWTIDE_META_KEY_V13] : '';
                            ?>
                            <tr class="cart-item" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                                <!-- View Picture -->
                                <td class="product-thumbnail">
                                    <?php
                                    if (!$product_permalink) {
                                        echo $product_thumbnail;
                                    } else {
                                        printf('<a href="%s">%s</a>', esc_url($product_permalink), $product_thumbnail);
                                    }
                                    ?>
                                </td>
                                
                                <!-- Selected Items -->
                                <td class="product-name">
                                    <?php
                                    if (!$product_permalink) {
                                        echo wp_kses_post($product_name);
                                    } else {
                                        echo wp_kses_post(sprintf('<a href="%s">%s</a>', esc_url($product_permalink), $product_name));
                                    }
                                    
                                    // 顯示產品屬性
                                    echo wc_get_formatted_cart_item_data($cart_item);
                                    ?>
                                </td>
                                
                                <!-- Estimate Purchasing Quantity -->
                                <td class="product-quantity">
                                    <input type="number" 
                                           class="quantity-input" 
                                           name="cart[<?php echo $cart_item_key; ?>][qty]" 
                                           value="<?php echo $cart_item['quantity']; ?>" 
                                           min="0" 
                                           step="1"
                                           data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                                </td>
                                
                                <!-- Estimate Purchasing Schedule -->
                                <td class="product-schedule">
                                    <select class="schedule-select" 
                                            name="cart[<?php echo $cart_item_key; ?>][schedule]"
                                            data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                                        <option value="">-- Please select --</option>
                                        <optgroup label="Short Term">
                                            <option value="< 1 month" <?php selected($schedule_value, '< 1 month'); ?>>< 1 month</option>
                                            <option value="1-3 months" <?php selected($schedule_value, '1-3 months'); ?>>1-3 months</option>
                                        </optgroup>
                                        <optgroup label="Mid Term">
                                            <option value="3-6 months" <?php selected($schedule_value, '3-6 months'); ?>>3-6 months</option>
                                            <option value="6-12 months" <?php selected($schedule_value, '6-12 months'); ?>>6-12 months</option>
                                        </optgroup>
                                        <optgroup label="Long Term">
                                            <option value="> 1 year" <?php selected($schedule_value, '> 1 year'); ?>>> 1 year</option>
                                        </optgroup>
                                    </select>
                                </td>
                                
                                <!-- Delete -->
                                <td class="product-remove">
                                    <a href="#" class="remove-item" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>" title="刪除此項目">
                                        <span class="dashicons dashicons-trash"></span>
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
            
            <!-- 客戶資訊表單 -->
            <div class="customer-info-section">
                <div class="form-section-intro">
                    <span><?php echo pll__('To provide you the best service, Please fill out the form below.', 'form_section_intro'); ?></span>
                </div>

                <h3><?php echo pll__('Information of Contact Person', 'form_contact_info'); ?></h3>

                <div class="form-row">
                    <div class="form-col">
                        <label for="billing_first_name"><?php echo pll__('Name', 'form_name_label'); ?> <span class="required"><?php echo pll__('*', 'form_required'); ?></span></label>
                        <input type="text" id="billing_first_name" name="billing_first_name" placeholder="<?php echo pll__('Name*', 'form_name_placeholder'); ?>" required>
                    </div>
                    <div class="form-col">
                        <label for="billing_last_name"><?php echo pll__('Last name', 'form_last_name_label'); ?> <span class="required"><?php echo pll__('*', 'form_required'); ?></span></label>
                        <input type="text" id="billing_last_name" name="billing_last_name" placeholder="<?php echo pll__('Last name*', 'form_last_name_placeholder'); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label for="contact_gender"><?php echo pll__('Gender', 'form_gender_label'); ?></label>
                        <select id="contact_gender" name="contact_gender">
                            <option value="Gender"><?php echo pll__('Gender', 'form_gender_placeholder'); ?></option>
                            <option value="Male"><?php echo pll__('Male', 'form_gender_male'); ?></option>
                            <option value="Female"><?php echo pll__('Female', 'form_gender_female'); ?></option>
                        </select>
                    </div>
                    <div class="form-col">
                        <label for="billing_email"><?php echo pll__('Email', 'form_email_label'); ?> <span class="required"><?php echo pll__('*', 'form_required'); ?></span></label>
                        <input type="email" id="billing_email" name="billing_email" placeholder="<?php echo pll__('E-mail*', 'form_email_placeholder'); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label for="contact_department"><?php echo pll__('Department', 'form_department_label'); ?> <span class="required"><?php echo pll__('*', 'form_required'); ?></span></label>
                        <input type="text" id="contact_department" name="contact_department" placeholder="<?php echo pll__('Department*', 'form_department_placeholder'); ?>" required>
                    </div>
                    <div class="form-col">
                        <label for="contact_job_title"><?php echo pll__('Job Title', 'form_job_title_label'); ?> <span class="required"><?php echo pll__('*', 'form_required'); ?></span></label>
                        <input type="text" id="contact_job_title" name="contact_job_title" placeholder="<?php echo pll__('Job Title*', 'form_job_title_placeholder'); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col full-width">
                        <label class="checkbox-label">
                            <input type="checkbox" name="contact_subscribe" value="yes">
                            <label><?php echo pll__('Please mail me detail information of products, services and news of events.', 'form_subscribe_label'); ?></label>
                        </div>
                    </div>
                </div>

                <h3><?php echo pll__('Company Information', 'form_company_info'); ?></h3>

                <div class="form-row">
                    <div class="form-col">
                        <label for="billing_company"><?php echo pll__('Company', 'form_company_label'); ?> <span class="required">*</span></label>
                        <input type="text" id="billing_company" name="billing_company" placeholder="<?php echo pll__('Company*', 'form_company_required'); ?>" required>
                    </div>
                    <div class="form-col">
                        <label for="company_url"><?php echo pll__('URL', 'form_url_label'); ?></label>
                        <input type="url" id="company_url" name="company_url" placeholder="<?php echo pll__('URL', 'form_url_label'); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <label for="company_industry"><?php echo pll__('Industry', 'form_industry_label'); ?></label>
                        <select id="company_industry" name="company_industry">
                            <option value="Industry"><?php echo pll__('Industry', 'form_industry_placeholder'); ?></option>
                            <option value="Audio"><?php echo pll__('Audio', 'form_audio'); ?></option>
                            <option value="Video"><?php echo pll__('Video', 'form_video'); ?></option>
                            <option value="Communication"><?php echo pll__('Communication', 'form_communication'); ?></option>
                            <option value="Computer"><?php echo pll__('Computer', 'form_computer'); ?></option>
                            <option value="Medical"><?php echo pll__('Medical', 'form_medical'); ?></option>
                            <option value="Electronic"><?php echo pll__('Electronic', 'form_electronic'); ?></option>
                            <option value="Transportation"><?php echo pll__('Transportation', 'form_transportation'); ?></option>
                            <option value="Instrument"><?php echo pll__('Instrument', 'form_instrument'); ?></option>
                            <option value="Manufacturing"><?php echo pll__('Manufacturing', 'form_manufacturing'); ?></option>
                            <option value="Retail"><?php echo pll__('Retail', 'form_retail'); ?></option>
                            <option value="Healthcare"><?php echo pll__('Healthcare', 'form_healthcare'); ?></option>
                            <option value="Automotive"><?php echo pll__('Automotive', 'form_automotive'); ?></option>
                            <option value="Aerospace">Aerospace</option>
                            <option value="Energy">Energy</option>
                            <option value="Construction">Construction</option>
                            <option value="Education">Education</option>
                            <option value="Finance">Finance</option>
                            <option value="Hospitality">Hospitality</option>
                            <option value="Food and Beverage">Food and Beverage</option>
                            <option value="Pharmaceutical">Pharmaceutical</option>
                        </select>
                    </div>
                    <div class="form-col">
                        <label for="company_main_product"><?php echo pll__('Main Product', 'form_main_product_label'); ?></label>
                        <input type="text" id="company_main_product" name="company_main_product" placeholder="<?php echo pll__('Main Product', 'form_main_product_placeholder'); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col full-width">
                        <label for="billing_address"><?php echo pll__('Address', 'form_address_label'); ?></label>
                        <input type="text" id="billing_address" name="billing_address" placeholder="<?php echo pll__('Address', 'form_address_placeholder'); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label for="billing_state"><?php echo pll__('State', 'form_state_label'); ?></label>
                        <input type="text" id="billing_state" name="billing_state" placeholder="<?php echo pll__('State', 'form_state_placeholder'); ?>">
                    </div>
                    <div class="form-col">
                        <label for="billing_postcode"><?php echo pll__('Postal Code', 'form_postcode_label'); ?></label>
                        <input type="text" id="billing_postcode" name="billing_postcode" placeholder="<?php echo pll__('Postal Code', 'form_postcode_placeholder'); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col full-width">
                        <label for="billing_country"><?php echo pll__('Country', 'form_country_label'); ?> <span class="required"><?php echo pll__('*', 'form_required'); ?></span></label>
                        <select id="billing_country" name="billing_country" required>
                            <option value="Taiwan"><?php echo pll__('Taiwan', 'form_country_taiwan'); ?></option>
                            <option value="United States"><?php echo pll__('United States', 'form_country_us'); ?></option>
                            <option value="Canada"><?php echo pll__('Canada', 'form_country_canada'); ?></option>
                            <option value="United Kingdom"><?php echo pll__('United Kingdom', 'form_country_uk'); ?></option>
                            <option value="Australia"><?php echo pll__('Australia', 'form_country_australia'); ?></option>
                            <option value="Germany"><?php echo pll__('Germany', 'form_country_germany'); ?></option>
                            <option value="France"><?php echo pll__('France', 'form_country_france'); ?></option>
                            <option value="Japan"><?php echo pll__('Japan', 'form_country_japan'); ?></option>
                            <option value="China"><?php echo pll__('China', 'form_country_china'); ?></option>
                            <option value="India"><?php echo pll__('India', 'form_country_india'); ?></option>
                            </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label for="billing_phone"><?php echo pll__('Telephone', 'form_phone_label'); ?> <span class="required"><?php echo pll__('*', 'form_required'); ?></span></label>
                        <input type="tel" id="billing_phone" name="billing_phone" placeholder="<?php echo pll__('Telephone*', 'form_phone_placeholder'); ?>" required>
                    </div>
                    <div class="form-col">
                        <label for="company_fax"><?php echo pll__('Fax', 'form_fax_label'); ?> <span class="required"><?php echo pll__('*', 'form_required'); ?></span></label>
                        <input type="tel" id="company_fax" name="company_fax" placeholder="<?php echo pll__('Fax*', 'form_fax_placeholder'); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col full-width">
                        <label><?php echo pll__('Business Type', 'form_business_type_label'); ?></label>
                        <div class="checkbox-group checkbox-group-inline">
                            <label><input type="checkbox" name="business_type[]" value="Importer"><?php echo pll__('Importer', 'form_business_importer'); ?></label>
                            <label><input type="checkbox" name="business_type[]" value="Distributor"><?php echo pll__('Distributor', 'form_business_distributor'); ?></label>
                            <label><input type="checkbox" name="business_type[]" value="Exporter"><?php echo pll__('Exporter', 'form_business_exporter'); ?></label>
                            <label><input type="checkbox" name="business_type[]" value="Trader"><?php echo pll__('Trader', 'form_business_trader'); ?></label>
                            <label><input type="checkbox" name="business_type[]" value="Wholesaler"><?php echo pll__('Wholesaler', 'form_business_wholesaler'); ?></label>
                            <label><input type="checkbox" name="business_type[]" value="Retailer"><?php echo pll__('Retailer', 'form_business_retailer'); ?></label>
                            <label><input type="checkbox" name="business_type[]" value="Agent"><?php echo pll__('Agent', 'form_business_agent'); ?></label>
                            <label><input type="checkbox" name="business_type[]" value="Manufacturer"><?php echo pll__('Manufacturer', 'form_business_manufacturer'); ?></label>
                            <label><input type="checkbox" name="business_type[]" value="Trading Company"><?php echo pll__('Trading Company', 'form_business_trading_company'); ?></label>
                            <label><input type="checkbox" name="business_type[]" value="Others"><?php echo pll__('Others', 'form_business_others'); ?></label>
                        </div>
                    </div>
                </div>

                <h3><?php echo pll__('Others', 'form_others'); ?></h3>
                
                <div class="form-row">
                    <div class="form-col full-width">
                        <label style="font-weight: bold;"><?php echo pll__('Request of detail information', 'form_request_details_label'); ?></label>
                         <div class="checkbox-group checkbox-group-block">
                            <label><input type="checkbox" name="request_details[]" value="FOB prices (for minimum order quantity)"><?php echo pll__('FOB prices (for minimum order quantity)', 'form_fob_prices'); ?></label>
                            <label><input type="checkbox" name="request_details[]" value="Minimum order quantity"><?php echo pll__('Minimum order quantity', 'form_min_order_qty'); ?></label>
                            <label><input type="checkbox" name="request_details[]" value="Lead Time"><?php echo pll__('Lead Time', 'form_lead_time'); ?></label>
                            <label><input type="checkbox" name="request_details[]" value="Shipping Date"><?php echo pll__('Shipping Date', 'form_shipping_date'); ?></label>
                            <label><input type="checkbox" name="request_details[]" value="Expected Delivery Date"><?php echo pll__('Expected Delivery Date', 'form_delivery_date'); ?></label>
                            <label><input type="checkbox" name="request_details[]" value="Sample availability/Cost"><?php echo pll__('Sample availability/Cost', 'form_sample_info'); ?></label>
                            <label><input type="checkbox" name="request_details[]" value="Company Brochure"><?php echo pll__('Company Brochure', 'form_company_brochure'); ?></label>
                            <label><input type="checkbox" name="request_details[]" value="International standards met"><?php echo pll__('International standards met', 'form_international_standards'); ?></label>
                            <label><input type="checkbox" name="request_details[]" value="Others"><?php echo pll__('Others', 'form_others_info'); ?></label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col full-width">
                        <label for="order_comments"><?php echo pll__('Special Comment', 'form_special_comment_label'); ?></label>
                        <textarea id="order_comments" name="order_comments" rows="4" placeholder="<?php echo pll__('Special Comment', 'form_special_comment_placeholder'); ?>"></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col full-width privacy-policy-notice">
                        <span><?php echo pll__('By submitting your contact information, you acknowledge that you consent to our processing data in accordance with the Privacy and Cookie Policy.', 'form_privacy_policy'); ?></span>
                    </div>
                </div>

            </div>
            <div class="cart-actions">
                
                <button type="submit" class="button button-primary submit-inquiry"><?php echo pll__('SUBMIT', 'form_submit'); ?></button>
            </div>
    <!-- CSS 樣式 -->
<style>
    /* --- Start of Original Plugin Styles --- */
    .newtide-custom-cart {
        max-width: 1200px; 
        margin: 0 auto; 
        padding: 20px; 
    }
    
    .newtide-cart-table {
        width: 100%; 
        border-collapse: collapse; 
        margin-bottom: 30px; 
        background: #fff; 
        box-shadow: 0 1px 3px rgba(0,0,0,0.1); 
    }
    
    .newtide-cart-table th,
    .newtide-cart-table td {
        padding: 15px; 
        text-align: left; 
        border-bottom: 1px solid #e5e5e5; 
    }
    
    .newtide-cart-table th {
        background: #f8f9fa; 
        font-weight: 600; 
        color: #333; 
    }
    
    .newtide-cart-table tbody tr:hover {
        background: #f9f9f9; 
    }
    
    .product-thumbnail img {
        width: 80px; 
        height: auto; 
        border: 1px solid #e5e5e5; 
    }
    
    .product-name {
        min-width: 200px; 
    }
    
    .product-name a {
        text-decoration: none; 
        color: #333; 
        font-weight: 500; 
    }
    
    .product-name a:hover {
        color: #0073aa; 
    }
    
    .quantity-input {
        width: 80px; 
        padding: 8px; 
        border: 1px solid #ddd; 
        border-radius: 4px; 
        text-align: center; 
    }
    
    .schedule-select {
        width: 150px; 
        padding: 8px; 
        border: 1px solid #ddd; 
        border-radius: 4px; 
    }
    
    .remove-item {
        color: #dc3545; 
        text-decoration: none; 
        font-size: 20px; 
    }
    
    .remove-item:hover {
        color: #c82333; 
    }

    .cart-actions {
        text-align: right; 
        margin-top: 20px; 
    }
    
    .button {
        padding: 12px 24px; 
        border: none; 
        border-radius: 4px; 
        font-size: 16px; 
        cursor: pointer; 
        text-decoration: none; 
        display: inline-block; 
        margin-left: 10px; 
        transition: background-color 0.3s; 
    }
    
    .button:first-child {
        margin-left: 0; 
    }
    
    .button.update-cart {
        background: #6c757d; 
        color: white; 
    }
    
    .button.update-cart:hover {
        background: #5a6268; 
    }
    
    .button-primary {
        background: #007cba; 
        color: white; 
    }
    
    .button-primary:hover {
        background: #005a87; 
    }
    
    .empty-cart {
        text-align: center; 
        padding: 50px; 
        background: #fff; 
        box-shadow: 0 1px 3px rgba(0,0,0,0.1); 
    }
    
    .empty-cart p {
        font-size: 18px; 
        margin-bottom: 20px; 
        color: #666; 
    }
    
    @media (max-width: 768px) {
        .newtide-cart-table {
            font-size: 14px; 
        }
        
        .newtide-cart-table th,
        .newtide-cart-table td {
            padding: 10px 5px; 
        }
        
        .product-thumbnail img {
            width: 60px; 
        }
        
        .quantity-input,
        .schedule-select {
            width: 100%; 
        }
        
        .form-row {
            flex-direction: column; 
        }
        
        .cart-actions {
            text-align: center; 
        }
        
        .button {
            display: block; 
            width: 100%; 
            margin: 10px 0; 
        }
    }
    /* --- End of Original Plugin Styles --- */


    /* --- Newtide Inquiry Form Styles (Final Version with Alignment Fix) --- */

    /* 表單區塊的整體間距和標題 */
    .newtide-custom-cart .customer-info-section {
        background: #fff; 
        padding: 30px; 
        margin-bottom: 20px; 
        box-shadow: 0 1px 3px rgba(0,0,0,0.1); 
    }

    /* 模擬 JSON 中的 H3 樣式 */
    .newtide-custom-cart .customer-info-section h3 {
        margin-top: 20px;
        margin-bottom: 25px; 
        background-color: #f5f5f5;
        padding: 15px;
        color: #23262F;
        font-family: "Noto Sans TC", sans-serif;
        font-size: 20px;
        font-style: normal;
        font-weight: 500; 
        line-height: 24px;
    }
    .newtide-custom-cart .customer-info-section h3:first-of-type {
        margin-top: 0; 
    }

    /* 欄位標籤 Label */
    .newtide-custom-cart .form-col label {
        display: block; 
        margin-bottom: 5px; 
        font-weight: 400;
        font-family: "Noto Sans TC", sans-serif;
        font-size: 16px;
        color: #8F8F8F;
    }

    /* 針對 "Request of detail information" 的粗體標籤 */
    .newtide-custom-cart .form-col label[style*="font-weight: bold"] {
        color: #8F8F8F;
        font-weight: bold; 
    }

    /* 所有文字輸入框、下拉選單、文字區域的通用樣式 */
    .newtide-custom-cart .form-col input[type="text"],
    .newtide-custom-cart .form-col input[type="email"],
    .newtide-custom-cart .form-col input[type="tel"],
    .newtide-custom-cart .form-col input[type="url"],
    .newtide-custom-cart .form-col select,
    .newtide-custom-cart .form-col textarea {
        width: 100%; 
        padding: 12px 15px;
        border: 1px solid #E6E8EC;
        border-radius: 8px;
        font-size: 14px; 
        color: #69727D;
        background-color: #fff;
        transition: border-color 0.3s;
    }

    .newtide-custom-cart .form-col input:focus,
    .newtide-custom-cart .form-col select:focus,
    .newtide-custom-cart .form-col textarea:focus {
        border-color: #69727d !important;
        outline: none;
    }

    /* --- Checkbox 樣式修正 v2 --- */

    /* Checkbox Label 通用樣式 */
    .newtide-custom-cart .checkbox-group label {
        font-weight: normal;
        cursor: pointer;
        color: #8F8F8F;
    }

    /* 內行樣式 (Business Type) - 垂直對齊修正 */
    .newtide-custom-cart .checkbox-group-inline {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 20px;
    }
    .newtide-custom-cart .checkbox-group-inline label {
        white-space: nowrap; /* 防止標籤文字換行 */
    }
    .newtide-custom-cart .checkbox-group-inline input[type="checkbox"] {
        margin-right: 8px;
        vertical-align: middle; /* 關鍵：讓勾選框與文字垂直置中對齊 */
    }

    /* 區塊樣式 (Request of detail information) */
    .newtide-custom-cart .checkbox-group-block label {
        display: block;
        margin-bottom: 12px;
    }
    .newtide-custom-cart .checkbox-group-block label:last-child {
        margin-bottom: 0;
    }
    .newtide-custom-cart .checkbox-group-block input[type="checkbox"] {
        margin-right: 8px;
        vertical-align: middle;
    }
    /* --- Checkbox 樣式修正結束 --- */


    /* 隱私權政策提示文字 */
    .newtide-custom-cart .privacy-policy-notice span {
        font-size: 13px;
        color: #868686;
    }
    
    .newtide-custom-cart .form-row {
        display: flex; 
        gap: 20px; 
        margin-bottom: 25px;
    }

    .newtide-custom-cart .form-col {
        flex: 1; 
    }

    .newtide-custom-cart .form-col.full-width {
        flex: 1 0 100%; 
    }

</style>
    
    <!-- JavaScript -->
    <script type="text/javascript">
    jQuery(function($) {
        // 更新數量
        $(document).on('change', '.quantity-input', function() {
            var $input = $(this);
            var cartItemKey = $input.data('cart-item-key');
            var quantity = $input.val();
            
            if (quantity == 0) {
                if (confirm('確定要從詢價單中移除此項目嗎？')) {
                    removeItem(cartItemKey);
                } else {
                    $input.val(1);
                }
            }
        });
        
        // 更新 Schedule
        $(document).on('change', '.schedule-select', function() {
            var $select = $(this);
            var cartItemKey = $select.data('cart-item-key');
            var scheduleValue = $select.val();
            
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                data: {
                    action: 'newtide_update_schedule_v13',
                    security: '<?php echo wp_create_nonce('newtide-schedule-nonce-v13'); ?>',
                    cart_item_key: cartItemKey,
                    schedule_value: scheduleValue
                },
                success: function(response) {
                    console.log('Schedule updated:', response);
                }
            });
        });
        
        // 刪除項目
        $(document).on('click', '.remove-item', function(e) {
            e.preventDefault();
            var cartItemKey = $(this).data('cart-item-key');
            
            if (confirm('確定要從詢價單中移除此項目嗎？')) {
                removeItem(cartItemKey);
            }
        });
        
        // 刪除項目函式
        function removeItem(cartItemKey) {
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                data: {
                    action: 'newtide_remove_cart_item',
                    security: '<?php echo $nonce; ?>',
                    cart_item_key: cartItemKey
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        }
        
        // 更新購物車
        $('.update-cart').on('click', function() {
            var updates = [];
            
            $('.quantity-input').each(function() {
                var $input = $(this);
                updates.push({
                    key: $input.data('cart-item-key'),
                    quantity: $input.val()
                });
            });
            
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                data: {
                    action: 'newtide_update_cart_quantities',
                    security: '<?php echo $nonce; ?>',
                    updates: updates
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        });
    });
    </script>
    
    <?php
    return ob_get_clean();



// --- AJAX: 移除購物車項目 ---
add_action('wp_ajax_newtide_remove_cart_item', 'newtide_ajax_remove_cart_item');
add_action('wp_ajax_nopriv_newtide_remove_cart_item', 'newtide_ajax_remove_cart_item');

function newtide_ajax_remove_cart_item() {
    check_ajax_referer('newtide-custom-cart-nonce', 'security');
    
    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
    
    if (WC()->cart->remove_cart_item($cart_item_key)) {
        wp_send_json_success('Item removed');
    } else {
        wp_send_json_error('Failed to remove item');
    }
}

// --- AJAX: 更新購物車數量 ---
add_action('wp_ajax_newtide_update_cart_quantities', 'newtide_ajax_update_cart_quantities');
add_action('wp_ajax_nopriv_newtide_update_cart_quantities', 'newtide_ajax_update_cart_quantities');

function newtide_ajax_update_cart_quantities() {
    check_ajax_referer('newtide-custom-cart-nonce', 'security');
    
    $updates = $_POST['updates'];
    
    foreach ($updates as $update) {
        $cart_item_key = sanitize_text_field($update['key']);
        $quantity = intval($update['quantity']);
        
        if ($quantity > 0) {
            WC()->cart->set_quantity($cart_item_key, $quantity);
        } else {
            WC()->cart->remove_cart_item($cart_item_key);
        }
    }
    
    wp_send_json_success('Cart updated');
}

// --- 處理詢價單提交 ---
add_action('init', 'newtide_handle_custom_checkout');

function newtide_handle_custom_checkout() {
    // Nonce and cart empty checks... (same as before)
    if (!isset($_POST['newtide_checkout_nonce']) || !wp_verify_nonce($_POST['newtide_checkout_nonce'], 'newtide-custom-checkout')) { return; }
    if (WC()->cart->is_empty()) { wc_add_notice('Your inquiry cart is empty.', 'error'); return; }

    newtide_debug_log_v13('表單提交開始', $_POST);

    // 接收並過濾所有表單欄位... (same as before)
    $post_data = $_POST;
    $billing_first_name = sanitize_text_field($post_data['billing_first_name'] ?? '');
    $billing_last_name  = sanitize_text_field($post_data['billing_last_name'] ?? '');
    $contact_gender     = sanitize_text_field($post_data['contact_gender'] ?? '');
    $billing_email      = sanitize_email($post_data['billing_email'] ?? '');
    $contact_department = sanitize_text_field($post_data['contact_department'] ?? '');
    $contact_job_title  = sanitize_text_field($post_data['contact_job_title'] ?? '');
    $contact_subscribe  = isset($post_data['contact_subscribe']) ? 'Yes' : 'No';
    $billing_company    = sanitize_text_field($post_data['billing_company'] ?? '');
    $company_url        = esc_url_raw($post_data['company_url'] ?? '');
    $company_industry   = sanitize_text_field($post_data['company_industry'] ?? '');
    $company_main_product = sanitize_text_field($post_data['company_main_product'] ?? '');
    $billing_address    = sanitize_text_field($post_data['billing_address'] ?? '');
    $billing_state      = sanitize_text_field($post_data['billing_state'] ?? '');
    $billing_postcode   = sanitize_text_field($post_data['billing_postcode'] ?? '');
    $billing_country    = sanitize_text_field($post_data['billing_country'] ?? '');
    $billing_phone      = sanitize_text_field($post_data['billing_phone'] ?? '');
    $company_fax        = sanitize_text_field($post_data['company_fax'] ?? '');
    $order_comments = sanitize_textarea_field($post_data['order_comments'] ?? '');
    $business_type_array = isset($post_data['business_type']) && is_array($post_data['business_type']) ? array_map('sanitize_text_field', $post_data['business_type']) : [];
    $business_type_string = implode(', ', $business_type_array);
    $request_details_array = isset($post_data['request_details']) && is_array($post_data['request_details']) ? array_map('sanitize_text_field', $post_data['request_details']) : [];
    $request_details_string = implode(', ', $request_details_array);

    // 驗證必填欄位... (same as before)
    $required_fields = [ 'Name' => $billing_first_name, 'Last name' => $billing_last_name, 'Email' => $billing_email, 'Department' => $contact_department, 'Job Title' => $contact_job_title, 'Company' => $billing_company, 'Country' => $billing_country, 'Telephone' => $billing_phone, 'Fax' => $company_fax ];
    foreach ($required_fields as $label => $value) {
        if (empty($value)) {
            wc_add_notice(sprintf('Please fill in all required fields. "%s" is missing.', $label), 'error');
        }
    }
    if (wc_notice_count('error') > 0) { return; }

    
    // 建立訂單並儲存所有資料
    $order = wc_create_order();
    $order->set_billing_first_name($billing_first_name);
    $order->set_billing_last_name($billing_last_name);
    $order->set_billing_company($billing_company);
    $order->set_billing_email($billing_email);
    $order->set_billing_phone($billing_phone);
    $order->set_billing_address_1($billing_address);
    $order->set_billing_state($billing_state);
    $order->set_billing_postcode($billing_postcode);
    $order->set_billing_country($billing_country);
    $order->update_meta_data('Gender', $contact_gender);
    $order->update_meta_data('Department', $contact_department);
    $order->update_meta_data('Job Title', $contact_job_title);
    $order->update_meta_data('Subscribe to newsletter', $contact_subscribe);
    $order->update_meta_data('Company URL', $company_url);
    $order->update_meta_data('Industry', $company_industry);
    $order->update_meta_data('Main Product', $company_main_product);
    $order->update_meta_data('Fax', $company_fax);
    $order->update_meta_data('Business Type', $business_type_string);
    $order->update_meta_data('Request of detail information', $request_details_string);
    if (!empty($order_comments)) { $order->set_customer_note($order_comments); }
    
    // 將購物車項目加入訂單
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        $item_id = $order->add_product($product, $cart_item['quantity']);
        
        // --- **關鍵修正**：使用統一的 meta_key ---
        if ($item_id && !empty($cart_item[NEWTIDE_META_KEY_V13])) {
            wc_add_order_item_meta($item_id, 'Estimate Purchasing Schedule', $cart_item[NEWTIDE_META_KEY_V13]);
        }
    }
    
    $order->set_status('processing');
    $order->set_total(0);
    $order->calculate_totals();
    $order_id = $order->save(); // 儲存並取得 order_id
    // === 設定付款方式與狀態（必要條件） ===
$order->set_payment_method('bacs'); // 可自定義為你網站上的付款代碼
$order->update_status('processing'); // 或設為 'on-hold', 'pending' 等

// === 儲存訂單 ===
$order_id = $order->save();
newtide_debug_log_v13('訂單資料儲存完畢', ['order_id' => $order_id]);

// === 延遲觸發通知信：確保 mailer 完全初始化 ===
add_action('shutdown', function() use ($order_id) {
    $mailer = WC()->mailer();
    $emails = $mailer->get_emails();

    if (!empty($emails['WC_Email_New_Order'])) {
        $emails['WC_Email_New_Order']->trigger($order_id);
        newtide_debug_log_v13('訂單通知信已觸發（於 shutdown）', ['order_id' => $order_id]);
    } else {
        newtide_debug_log_v13('訂單通知信觸發失敗：找不到 WC_Email_New_Order', ['order_id' => $order_id]);
    }
});

    
    
    newtide_debug_log_v13('訂單資料已儲存完畢', array('order_id' => $order_id));
    
    // --- 郵件觸發 (修正版) ---
    $mailer = WC()->mailer();
    $recipient = get_option('woocommerce_new_order_recipient');
    if (!empty($recipient)) {
        $emails = $mailer->get_emails();
        if (!empty($emails['WC_Email_New_Order'])) {
            $emails['WC_Email_New_Order']->trigger($order_id);
            newtide_debug_log_v13('新訂單郵件已觸發', array('order_id' => $order_id, 'recipient' => $recipient));
        } else {
             newtide_debug_log_v13('郵件觸發失敗：找不到 WC_Email_New_Order 樣板', array('order_id' => $order_id));
        }
    } else {
        newtide_debug_log_v13('郵件觸發失敗：未設定收件者', array('order_id' => $order_id));
    }
    
    WC()->cart->empty_cart();
    
    // 跳轉
    $thank_you_page_url = get_permalink( get_page_by_path( 'thankyou' ) );
    if ($thank_you_page_url) {
        $redirect_url = add_query_arg('inquiry-received', $order_id, $thank_you_page_url);
        wp_redirect($redirect_url);
        exit;
    }
}

add_action('plugins_loaded', function () {
    load_plugin_textdomain('newtide-plugin', false, dirname(plugin_basename(__FILE__)) . '/languages');
});
}

