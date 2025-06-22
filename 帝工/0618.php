<?php
/*
 * Plugin Name: Newtide 帝公 自訂客製化功能插件
 * Plugin URI: https://www.newtide.com.tw/
 * Description: 整合前端購物車欄位與後台訂單顯示功能的完整版插件（含日誌檢視器）。
 * Version: 14.0 - Multi-Hook Storage
 * Author: CGLandmark Studio
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 註冊 Polylang 可翻譯字符串
function newtide_register_polylang_strings() {
    if (!function_exists('pll_register_string')) {
        return;
    }

    // 基本表單字段
    $form_strings = array(
        'Name',
        'Last name',
        'Company',
        'URL',
        'Main Product',
        'FOB prices (for minimum order quantity)',
        'Minimum order quantity',
        'Lead Time',
        'Shipping Date',
        'Expected Delivery Date',
        'Sample availability/Cost',
        'Company Brochure',
        'International standards met',
        'Others'
    );
    
    // 業務類型
    $business_types = array(
        'Importer',
        'Distributor',
        'Exporter',
        'Trader',
        'Wholesaler',
        'Retailer',
        'Agent',
        'Manufacturer',
        'Trading Company',
        'Others'
    );
    
    // 國家/地區名稱
    $country_strings = array(
        'Taiwan',
        'United States',
        'Canada',
        'United Kingdom',
        'Australia',
        'Germany',
        'France',
        'Japan',
        'China',
        'India'
    );
    
    // 行業名稱
    $industry_strings = array(
        'Audio',
        'Video',
        'Communication',
        'Computer',
        'Medical',
        'Electronic',
        'Transportation',
        'Instrument',
        'Manufacturing',
        'Retail',
        'Healthcare',
        'Automotive',
        'Aerospace',
        'Energy',
        'Construction',
        'Education',
        'Finance',
        'Hospitality',
        'Food and Beverage',
        'Pharmaceutical'
    );
    
    // 註冊所有字符串
    $all_strings = array_merge(
        $form_strings,
        $business_types,
        $country_strings,
        $industry_strings
    );
    
    foreach ($all_strings as $string) {
        pll_register_string($string, $string, 'newtide-plugin');
    }
}

// 在插件初始化時註冊字符串
add_action('init', 'newtide_register_polylang_strings');

// 初始化插件
add_action('plugins_loaded', 'newtide_final_plugin_init_v13');

function newtide_final_plugin_init_v13() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    // 註冊 Polylang 字串
    if (function_exists('pll_register_string')) {
        // 購物車相關字串
        pll_register_string('newtide_cart_empty', '您的詢價單目前是空的。', 'newtide-plugin');
        pll_register_string('newtide_continue_shopping', '繼續選購', 'newtide-plugin');
        pll_register_string('newtide_view_picture', 'View Picture', 'newtide-plugin');
        pll_register_string('newtide_selected_items', 'Selected Items', 'newtide-plugin');
        pll_register_string('newtide_estimate_quantity', 'Estimate Purchasing Quantity', 'newtide-plugin');
        pll_register_string('newtide_estimate_schedule', 'Estimate Purchasing Schedule', 'newtide-plugin');
        pll_register_string('newtide_delete', 'Delete', 'newtide-plugin');
        pll_register_string('newtide_please_select', '-- Please select --', 'newtide-plugin');
        pll_register_string('newtide_short_term', 'Short Term', 'newtide-plugin');
        pll_register_string('newtide_mid_term', 'Mid Term', 'newtide-plugin');
        pll_register_string('newtide_long_term', 'Long Term', 'newtide-plugin');
        pll_register_string('newtide_less_than_1_month', '< 1 month', 'newtide-plugin');
        pll_register_string('newtide_1_to_3_months', '1-3 months', 'newtide-plugin');
        pll_register_string('newtide_3_to_6_months', '3-6 months', 'newtide-plugin');
        pll_register_string('newtide_6_to_12_months', '6-12 months', 'newtide-plugin');
        pll_register_string('newtide_more_than_1_year', '> 1 year', 'newtide-plugin');

        // 表單相關字串
        pll_register_string('newtide_form_intro', 'To provide you the best service, Please fill out the form below.', 'newtide-plugin');
        pll_register_string('newtide_contact_info', 'Information of Contact Person :', 'newtide-plugin');
        pll_register_string('newtide_company_info', 'Company Information :', 'newtide-plugin');
        pll_register_string('newtide_others', 'Others :', 'newtide-plugin');
        pll_register_string('newtide_submit', 'SUBMIT', 'newtide-plugin');
        pll_register_string('newtide_required', 'required', 'newtide-plugin');
        pll_register_string('newtide_gender', 'Gender', 'newtide-plugin');
        pll_register_string('newtide_male', 'Male', 'newtide-plugin');
        pll_register_string('newtide_female', 'Female', 'newtide-plugin');
        pll_register_string('newtide_subscribe', 'Please mail me detail information of products, services and news of events.', 'newtide-plugin');
        pll_register_string('newtide_industry', 'Industry', 'newtide-plugin');
        pll_register_string('newtide_address', 'Address', 'newtide-plugin');
        pll_register_string('newtide_state', 'State', 'newtide-plugin');
        pll_register_string('newtide_postal_code', 'Postal Code', 'newtide-plugin');
        pll_register_string('newtide_country', 'Country', 'newtide-plugin');
        pll_register_string('newtide_telephone', 'Telephone', 'newtide-plugin');
        pll_register_string('newtide_fax', 'Fax', 'newtide-plugin');
        pll_register_string('newtide_business_type', 'Business Type', 'newtide-plugin');
        pll_register_string('newtide_request_details', 'Request of detail information', 'newtide-plugin');
        pll_register_string('newtide_special_comment', 'Special Comment', 'newtide-plugin');
        pll_register_string('newtide_privacy_policy', 'I acknowledge and agree that Newtide may collect, process, and use the personal data I have provided, within a reasonable and necessary scope and in accordance with applicable laws and regulations. Such data may be used for marketing communications, customer service, satisfaction surveys, and other business-related contact purposes.', 'newtide-plugin');
    }

    define('NEWTIDE_META_KEY_V13', '_estimate_purchasing_schedule');
    define('NEWTIDE_LABEL_V13', 'Estimate Purchasing Schedule');
    define('NEWTIDE_DEBUG_V13', true); // 開啟調試模式
    define('NEWTIDE_LOG_OPTION_V13', 'newtide_debug_logs_v13'); // 日誌儲存選項名稱

    // 調試日誌函數 - 儲存到資料庫
    if (!function_exists('newtide_debug_log_v13')) {
        function newtide_debug_log_v13($message, $data = null) {
            if (NEWTIDE_DEBUG_V13) {
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
    }

    newtide_debug_log_v13('插件初始化成功 (Plugin Initialized)');

    // --- 新增管理員選單 ---
    add_action('admin_menu', 'newtide_add_admin_menu_v13');

    // --- 前端 ---
    add_action('woocommerce_after_cart_item_name', 'newtide_add_schedule_field_to_cart_v13', 10, 2);
    add_filter('woocommerce_get_cart_item_from_session', 'newtide_get_schedule_from_session_v13', 20, 2);
    add_action('wp_footer', 'newtide_add_custom_ajax_script_v13');

    // --- AJAX Endpoints ---
    add_action('wp_ajax_newtide_update_schedule_v13', 'newtide_ajax_update_schedule_handler_v13');
    add_action('wp_ajax_nopriv_newtide_update_schedule_v13', 'newtide_ajax_update_schedule_handler_v13');
    add_action('wp_ajax_newtide_clear_logs_v13', 'newtide_ajax_clear_logs_v13');
    add_action('wp_ajax_newtide_run_diagnostic_v13', 'newtide_ajax_run_diagnostic_v13');

    // --- 儲存邏輯 - 使用多個 hooks 確保資料儲存 ---
    add_action( 'woocommerce_checkout_create_order_line_item', 'newtide_add_custom_data_to_order_item_v13', 10, 4 );
    add_action( 'woocommerce_new_order_item', 'newtide_save_order_item_meta_v13', 10, 3 );
    add_action( 'woocommerce_add_order_item_meta', 'newtide_legacy_save_order_item_meta_v13', 10, 3 );
    add_filter( 'woocommerce_hidden_order_itemmeta', 'newtide_hide_order_item_meta_v13' );

    // --- 後台與前端顯示 ---
    add_action('woocommerce_before_order_itemmeta', 'newtide_display_admin_order_item_field_v13', 10, 2);
    add_action('woocommerce_saved_order_items', 'newtide_save_admin_order_item_field_v13', 10, 2);
    add_action('woocommerce_order_item_meta_end', 'newtide_display_schedule_in_order_details_v13', 10, 4);
    
    // --- 額外的調試顯示 ---
    // add_action('woocommerce_after_order_itemmeta', 'newtide_debug_display_all_meta_v13', 10, 2);
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
        <h1>Newtide 調試日誌</h1>
        
        <div style="margin: 20px 0;">
            <button id="newtide-clear-logs" class="button button-secondary">清除所有日誌</button>
            <button id="newtide-refresh-logs" class="button button-primary">重新整理</button>
            <button id="newtide-run-diagnostic" class="button button-secondary">執行診斷</button>
            <span id="newtide-log-status" style="margin-left: 20px;"></span>
        </div>
        
        <div id="newtide-diagnostic-result" style="display: none; margin: 20px 0; padding: 15px; background: #fff; border: 1px solid #ccc;">
            <h3>診斷結果</h3>
            <div id="diagnostic-content"></div>
        </div>
        
        <?php
        $logs = get_option(NEWTIDE_LOG_OPTION_V13, array());
        $logs = array_reverse($logs); // 最新的在上面
        
        if (empty($logs)) {
            echo '<p>目前沒有日誌資料。</p>';
        } else {
            ?>
            <div style="background: #f9f9f9; border: 1px solid #ddd; padding: 10px; max-height: 600px; overflow-y: auto;">
                <table class="widefat" style="background: white;">
                    <thead>
                        <tr>
                            <th style="width: 180px;">時間</th>
                            <th>訊息</th>
                            <th>資料</th>
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
                顯示 <?php echo count($logs); ?> 筆日誌（最多保留 500 筆）
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
    <?php
}

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
    echo '<option value="">' . esc_html(NEWTIDE_LABEL_V13) . '</option>';
    echo '<optgroup label="Short Term">';
    echo '<option value="< 1 month"' . selected($value, '< 1 month', false) . '>< 1 month</option>';
    echo '<option value="1-3 months"' . selected($value, '1-3 months', false) . '>1-3 months</option>';
    echo '</optgroup>';
    echo '<optgroup label="Mid Term">';
    echo '<option value="3-6 months"' . selected($value, '3-6 months', false) . '>3-6 months</option>';
    echo '<option value="6-12 months"' . selected($value, '6-12 months', false) . '>6-12 months</option>';
    echo '</optgroup>';
    echo '<optgroup label="Long Term">';
    echo '<option value="> 1 year"' . selected($value, '> 1 year', false) . '>> 1 year</option>';
    echo '</optgroup>';
    echo '</select>';
    echo '</div>';
}

function newtide_add_custom_ajax_script_v13() {
    // Only run on front-end. Previously limited to is_cart() which prevented script on custom cart shortcode pages.
    if ( is_admin() ) return;
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
    <?php
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
    // 檢查是否在管理員預覽郵件時執行
    if ( is_admin() && function_exists('get_current_screen') ) {
        $screen = get_current_screen();
        if ( $screen && $screen->base === 'admin-ajax' && isset( $_GET['preview_woocommerce_mail'] ) ) {
            return; // 在郵件預覽時跳過
        }
    }
    
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
    
    // 檢查 WooCommerce 和購物車是否可用
    if ( ! function_exists('WC') || ! is_object(WC()) || ! isset(WC()->cart) ) {
        newtide_debug_log_v13('WooCommerce 購物車不可用，跳過', array(
            'item_id' => $item_id,
            'order_id' => $order_id
        ));
        return;
    }
    
    try {
        // 嘗試從購物車 session 取得資料
        $cart = WC()->cart->get_cart();
        
        if ( ! empty( $cart ) ) {
            foreach ( $cart as $cart_item_key => $cart_item ) {
                if ( isset( $cart_item['product_id'] ) && $cart_item['product_id'] == $item->get_product_id() ) {
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
    } catch (Exception $e) {
        newtide_debug_log_v13('儲存訂單項目資料時發生錯誤', array(
            'error' => $e->getMessage(),
            'item_id' => $item_id,
            'order_id' => $order_id
        ));
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
	
	// >>> 在這裡加入日誌 <<<
    newtide_debug_log_v13('【讀取顯示】後台嘗試讀取訂單項目 Meta', [
        'item_id' => $item_id,
        'product_name' => $item->get_name(),
        'meta_key_used' => NEWTIDE_META_KEY_V13,
        'value_found' => $value ? $value : '找不到值或NULL'
    ]);

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
    // WooCommerce cart may not be initialized in certain contexts (e.g., REST preload).
    if ( null === WC()->cart ) {
        $diagnostic[] = '⚠ WooCommerce cart 尚未初始化。';
        $cart = array();
    } else {
        $cart = WC()->cart->get_cart();
    }
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
// Load AJAX handler script in footer when pages are rendered
add_action('wp_footer', 'newtide_add_custom_ajax_script_v13');

// --- 自訂購物車 Shortcode 函式 ---
function newtide_custom_cart_shortcode_v13($atts) {
    // 後台（區塊編輯器 REST 預載）或 WooCommerce 尚未初始化時，僅回傳占位文字，避免呼叫 WC() 造成 fatal error。
    // 如果在後台區塊編輯器（REST preload）或其它非前端環境，僅輸出占位內容以避免 WooCommerce 尚未初始化造成 fatal error。
    if ( ( is_admin() && ! wp_doing_ajax() ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        return '<p>'.esc_html__('Cart preview unavailable in editor.','newtide-plugin').'</p>';
    }

    // 確保 WooCommerce 已載入
    if (!class_exists('WooCommerce')) {
        return '<p>WooCommerce 未啟用。</p>';
    }
    
    // 獲取當前語言
    $current_lang = 'zh'; // 預設語言
    if (function_exists('pll_current_language')) {
        $current_lang = pll_current_language();
    }
    
    // 根據語言選擇相應的字串

    // --- 載入前端樣式（僅輸出一次）---
    static $newtide_cart_styles_printed = false;
    if ( ! $newtide_cart_styles_printed ) {
        $newtide_cart_styles_printed = true;
        echo <<<EOT
        <!-- Newtide Custom Cart Styles -->
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
            /* --- Newtide Inquiry Form Styles (trimmed for brevity) --- */
            .newtide-custom-cart .customer-info-section {background:#fff;padding:30px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.1);}        
            /* 其餘表單樣式沿用使用者提供內容，可在此繼續擴充 */
        </style>
        EOT;
    }

    $cart_empty_text = ($current_lang == 'zh') ? pll__('您的詢價單目前是空的。', 'newtide-plugin') : pll__('Your cart is currently empty.', 'newtide-plugin');
    $continue_shopping_text = ($current_lang == 'zh') ? pll__('繼續選購', 'newtide-plugin') : pll__('Continue Shopping', 'newtide-plugin');
    
    // 開始輸出緩衝
    ob_start();
    
    // 獲取購物車內容
    $cart = WC()->cart->get_cart();
    
    if (empty($cart)) {
        ?>
        <div class="newtide-custom-cart empty-cart">
            <p><?php echo esc_html($cart_empty_text); ?></p>
            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="button"><?php echo esc_html($continue_shopping_text); ?></a>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // 產生 nonce
    $nonce = wp_create_nonce('newtide-custom-cart-nonce');
    ?>
    
    <div class="newtide-custom-cart">
        <form class="newtide-cart-form" method="post">
            <?php wp_nonce_field('newtide-custom-checkout', 'newtide_checkout_nonce'); ?>
            
            <table class="newtide-cart-table">
                <thead>
                    <tr>
                        <th class="product-thumbnail"><?php echo pll__('View Picture', 'newtide-plugin'); ?></th>
                        <th class="product-name"><?php echo pll__('Selected Items', 'newtide-plugin'); ?></th>
                        <th class="product-quantity"><?php echo pll__('Estimate Purchasing Quantity', 'newtide-plugin'); ?></th>
                        <th class="product-schedule"><?php echo pll__('Estimate Purchasing Schedule', 'newtide-plugin'); ?></th>
                        <th class="product-remove"><?php echo pll__('Delete', 'newtide-plugin'); ?></th>
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
                                        <option value=""><?php echo pll__('-- Please select --', 'newtide-plugin'); ?></option>
                                        <optgroup label="<?php echo pll__('Short Term', 'newtide-plugin'); ?>">
                                            <option value="< 1 month" <?php selected($schedule_value, '< 1 month'); ?>><?php echo pll__('< 1 month', 'newtide-plugin'); ?></option>
                                            <option value="1-3 months" <?php selected($schedule_value, '1-3 months'); ?>><?php echo pll__('1-3 months', 'newtide-plugin'); ?></option>
                                        </optgroup>
                                        <optgroup label="<?php echo pll__('Mid Term', 'newtide-plugin'); ?>">
                                            <option value="3-6 months" <?php selected($schedule_value, '3-6 months'); ?>><?php echo pll__('3-6 months', 'newtide-plugin'); ?></option>
                                            <option value="6-12 months" <?php selected($schedule_value, '6-12 months'); ?>><?php echo pll__('6-12 months', 'newtide-plugin'); ?></option>
                                        </optgroup>
                                        <optgroup label="<?php echo pll__('Long Term', 'newtide-plugin'); ?>">
                                            <option value="> 1 year" <?php selected($schedule_value, '> 1 year'); ?>><?php echo pll__('> 1 year', 'newtide-plugin'); ?></option>
                                        </optgroup>
                                    </select>
                                </td>
                                
                                <!-- Delete -->
                                <td class="product-remove">
                                    <a href="#" class="remove-item" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>" title="<?php echo pll__('刪除此項目', 'newtide-plugin'); ?>">
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
                    <span><?php echo pll__('To provide you the best service, Please fill out the form below.', 'newtide-plugin'); ?></span>
                </div>

                <h3><?php echo pll__('Information of Contact Person :', 'newtide-plugin'); ?></h3>

                <div class="form-row">
                    <div class="form-col">
                        <label for="billing_first_name"><?php echo pll__('Name', 'newtide-plugin'); ?> <span class="required"><?php echo pll__('*', 'newtide-plugin'); ?></span></label>
                        <input type="text" id="billing_first_name" name="billing_first_name" placeholder="<?php echo pll__('Name*', 'newtide-plugin'); ?>" required>
                    </div>
                    <div class="form-col">
                        <label for="billing_last_name"><?php echo pll__('Last name', 'newtide-plugin'); ?> <span class="required"><?php echo pll__('*', 'newtide-plugin'); ?></span></label>
                        <input type="text" id="billing_last_name" name="billing_last_name" placeholder="<?php echo pll__('Last name*', 'newtide-plugin'); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label for="contact_gender"><?php echo pll__('Gender', 'newtide-plugin'); ?></label>
                        <select id="contact_gender" name="contact_gender">
                            <option value="Gender"><?php echo pll__('Gender', 'newtide-plugin'); ?></option>
                            <option value="Male"><?php echo pll__('Male', 'newtide-plugin'); ?></option>
                            <option value="Female"><?php echo pll__('Female', 'newtide-plugin'); ?></option>
                        </select>
                    </div>
                    <div class="form-col">
                        <label for="billing_email"><?php echo pll__('Email', 'newtide-plugin'); ?> <span class="required"><?php echo pll__('*', 'newtide-plugin'); ?></span></label>
                        <input type="email" id="billing_email" name="billing_email" placeholder="<?php echo pll__('E-mail*', 'newtide-plugin'); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col full-width">
                        <label class="checkbox-label">
                            <input type="checkbox" name="contact_subscribe" value="yes">
                            <?php echo pll__('Please mail me detail information of products, services and news of events.', 'newtide-plugin'); ?>
                        </label>
                    </div>
                </div>

                <h3><?php echo pll__('Company Information :', 'newtide-plugin'); ?></h3>

                <div class="form-row">
                    <div class="form-col">
                        <label for="billing_company"><?php echo pll__('Company', 'newtide-plugin'); ?> <span class="required"><?php echo pll__('*', 'newtide-plugin'); ?></span></label>
                        <input type="text" id="billing_company" name="billing_company" placeholder="<?php echo pll__('Company*', 'newtide-plugin'); ?>" required>
                    </div>
                    <div class="form-col">
                        <label for="company_url"><?php echo pll__('URL', 'newtide-plugin'); ?></label>
                        <input type="url" id="company_url" name="company_url" placeholder="<?php echo pll__('URL', 'newtide-plugin'); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <label for="company_industry"><?php echo pll__('Industry', 'newtide-plugin'); ?></label>
                        <select id="company_industry" name="company_industry">
                            <option value="Industry"><?php echo pll__('Industry', 'newtide-plugin'); ?></option>
                            <?php
                            // 定義行業選項數組
                            $industries = array(
                                'Audio' => pll__('Audio', 'newtide-plugin'),
                                'Video' => pll__('Video', 'newtide-plugin'),
                                'Communication' => pll__('Communication', 'newtide-plugin'),
                                'Computer' => pll__('Computer', 'newtide-plugin'),
                                'Medical' => pll__('Medical', 'newtide-plugin'),
                                'Electronic' => pll__('Electronic', 'newtide-plugin'),
                                'Transportation' => pll__('Transportation', 'newtide-plugin'),
                                'Instrument' => pll__('Instrument', 'newtide-plugin'),
                                'Manufacturing' => pll__('Manufacturing', 'newtide-plugin'),
                                'Retail' => pll__('Retail', 'newtide-plugin'),
                                'Healthcare' => pll__('Healthcare', 'newtide-plugin'),
                                'Automotive' => pll__('Automotive', 'newtide-plugin'),
                                'Aerospace' => pll__('Aerospace', 'newtide-plugin'),
                                'Energy' => pll__('Energy', 'newtide-plugin'),
                                'Construction' => pll__('Construction', 'newtide-plugin'),
                                'Education' => pll__('Education', 'newtide-plugin'),
                                'Finance' => pll__('Finance', 'newtide-plugin'),
                                'Hospitality' => pll__('Hospitality', 'newtide-plugin'),
                                'Food and Beverage' => pll__('Food and Beverage', 'newtide-plugin'),
                                'Pharmaceutical' => pll__('Pharmaceutical', 'newtide-plugin')
                            );
                            
                            // 輸出選項
                            foreach ($industries as $value => $label) {
                                echo sprintf(
                                    '<option value="%s">%s</option>',
                                    esc_attr($value),
                                    esc_html($label)
                                );
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-col">
                        <label for="company_main_product"><?php echo pll__('Main Product', 'newtide-plugin'); ?></label>
                        <input type="text" id="company_main_product" name="company_main_product" placeholder="<?php echo pll__('Main Product', 'newtide-plugin'); ?>">
                    </div>
                </div>

                <h3>客戶資訊</h3>
                
                <div class="form-row">
                    <div class="form-col">
                        <label for="billing_phone"><?php echo pll__('Telephone', 'newtide-plugin'); ?> <span class="required"><?php echo pll__('*', 'newtide-plugin'); ?></span></label>
                        <input type="tel" id="billing_phone" name="billing_phone" placeholder="<?php echo pll__('Telephone*', 'newtide-plugin'); ?>" required>
                    </div>
                    <div class="form-col">
                        <label for="company_fax"><?php echo pll__('Fax', 'newtide-plugin'); ?></label>
                        <input type="text" id="company_fax" name="company_fax" placeholder="<?php echo pll__('Fax', 'newtide-plugin'); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col full-width">
                        <label for="billing_address"><?php echo pll__('Address', 'newtide-plugin'); ?></label>
                        <input type="text" id="billing_address" name="billing_address" placeholder="<?php echo pll__('Address', 'newtide-plugin'); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <label for="billing_state"><?php echo pll__('State', 'newtide-plugin'); ?></label>
                        <input type="text" id="billing_state" name="billing_state" placeholder="<?php echo pll__('State', 'newtide-plugin'); ?>">
                    </div>
                    <div class="form-col">
                        <label for="billing_postcode"><?php echo pll__('Postal Code', 'newtide-plugin'); ?></label>
                        <input type="text" id="billing_postcode" name="billing_postcode" placeholder="<?php echo pll__('Postal Code', 'newtide-plugin'); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col full-width">
                        <label for="billing_country"><?php echo pll__('Country', 'newtide-plugin'); ?> <span class="required"><?php echo pll__('*', 'newtide-plugin'); ?></span></label>
                        <select id="billing_country" name="billing_country" required>
                            <?php
                            // 定義國家/地區選項數組
                            $countries = array(
                                'Taiwan' => pll__('Taiwan', 'newtide-plugin'),
                                'United States' => pll__('United States', 'newtide-plugin'),
                                'Canada' => pll__('Canada', 'newtide-plugin'),
                                'United Kingdom' => pll__('United Kingdom', 'newtide-plugin'),
                                'Australia' => pll__('Australia', 'newtide-plugin'),
                                'Germany' => pll__('Germany', 'newtide-plugin'),
                                'France' => pll__('France', 'newtide-plugin'),
                                'Japan' => pll__('Japan', 'newtide-plugin'),
                                'China' => pll__('China', 'newtide-plugin'),
                                'India' => pll__('India', 'newtide-plugin')
                            );
                            
                            // 輸出選項
                            foreach ($countries as $value => $label) {
                                echo sprintf(
                                    '<option value="%s">%s</option>',
                                    esc_attr($value),
                                    esc_html($label)
                                );
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col full-width">
                        <label><?php echo pll__('Business Type', 'newtide-plugin'); ?></label>
                        <div class="checkbox-group checkbox-group-inline">
                            <label><input type="checkbox" name="business_type[]" value="Importer"><?php echo pll__('Importer', 'newtide-plugin'); ?></label>
                            <label><input type="checkbox" name="business_type[]" value="Distributor"><?php echo pll__('Distributor', 'newtide-plugin'); ?></label>
                            <label><input type="checkbox" name="business_type[]" value="Exporter"><?php echo pll__('Exporter', 'newtide-plugin'); ?></label>
                            <label><input type="checkbox" name="business_type[]" value="Trader"><?php echo pll__('Trader', 'newtide-plugin'); ?></label>
                            <label><input type="checkbox" name="business_type[]" value="Wholesaler"><?php echo pll__('Wholesaler', 'newtide-plugin'); ?></label>
                            <label><input type="checkbox" name="business_type[]" value="Retailer"><?php echo pll__('Retailer', 'newtide-plugin'); ?></label>
                            <label><input type="checkbox" name="business_type[]" value="Agent"><?php echo pll__('Agent', 'newtide-plugin'); ?></label>
                            <label><input type="checkbox" name="business_type[]" value="Manufacturer"><?php echo pll__('Manufacturer', 'newtide-plugin'); ?></label>
                            <label><input type="checkbox" name="business_type[]" value="Trading Company"><?php echo pll__('Trading Company', 'newtide-plugin'); ?></label>
                            <label><input type="checkbox" name="business_type[]" value="Others"><?php echo pll__('Others', 'newtide-plugin'); ?></label>
                        </div>
                    </div>
                </div>

                <h3><?php echo pll__('Others :', 'newtide-plugin'); ?></h3>
                
                <div class="form-row">
                    <div class="form-col full-width">
                        <label style="font-weight: bold;"><?php echo pll__('Request of detail information', 'newtide-plugin'); ?></label>
                         <div class="checkbox-group checkbox-group-block">
                            <label><input type="checkbox" name="request_details[]" value="FOB prices (for minimum order quantity)"><?php echo pll__('FOB prices (for minimum order quantity)', 'newtide-plugin'); ?></label>
                            <label><input type="checkbox" name="request_details[]" value="Minimum order quantity"><?php echo pll__('Minimum order quantity', 'newtide-plugin'); ?></label>
                            <label><input type="checkbox" name="request_details[]" value="Lead Time"><?php echo pll__('Lead Time', 'newtide-plugin'); ?></label>
                            <label><input type="checkbox" name="request_details[]" value="Shipping Date"><?php echo pll__('Shipping Date', 'newtide-plugin'); ?></label>
                            <label><input type="checkbox" name="request_details[]" value="Expected Delivery Date"><?php echo pll__('Expected Delivery Date', 'newtide-plugin'); ?></label>
                            <label><input type="checkbox" name="request_details[]" value="Sample availability/Cost"><?php echo pll__('Sample availability/Cost', 'newtide-plugin'); ?></label>
                            <label><input type="checkbox" name="request_details[]" value="Company Brochure"><?php echo pll__('Company Brochure', 'newtide-plugin'); ?></label>
                            <label><input type="checkbox" name="request_details[]" value="International standards met"><?php echo pll__('International standards met', 'newtide-plugin'); ?></label>
                            <label><input type="checkbox" name="request_details[]" value="Others"><?php echo pll__('Others', 'newtide-plugin'); ?></label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col full-width">
                        <label for="order_comments"><?php echo pll__('Special Comment', 'newtide-plugin'); ?></label>
                        <textarea id="order_comments" name="order_comments" rows="4" placeholder="<?php echo pll__('Special Comment', 'newtide-plugin'); ?>"></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col full-width privacy-policy-notice">
                        <span><?php echo pll__('I acknowledge and agree that Newtide may collect, process, and use the personal data I have provided, within a reasonable and necessary scope and in accordance with applicable laws and regulations. Such data may be used for marketing communications, customer service, satisfaction surveys, and other business-related contact purposes.', 'newtide-plugin'); ?></span>
                    </div>
                </div>
            </div>
            <div class="cart-actions">
                <button type="submit" class="button button-primary submit-inquiry"><?php echo pll__('SUBMIT', 'newtide-plugin'); ?></button>
            </div>
        </form>
    </div>
    
    <?php
    return ob_get_clean();
}

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

    // 驗證必填欄位
    $required_fields = [
        'Name' => $billing_first_name, 
        'Last name' => $billing_last_name, 
        'Email' => $billing_email, 
        'Company' => $billing_company, 
        'Country' => $billing_country, 
        'Telephone' => $billing_phone
    ];
    
    $has_errors = false;
    foreach ($required_fields as $label => $value) {
        if (empty(trim($value))) {
            wc_add_notice(sprintf(__('Please fill in all required fields. "%s" is missing.', 'woocommerce'), $label), 'error');
            $has_errors = true;
        }
    }
    
    // 驗證電子郵件格式
    if (!is_email($billing_email)) {
        wc_add_notice(__('Please enter a valid email address.', 'woocommerce'), 'error');
        $has_errors = true;
    }
    
    if ($has_errors) { 
        newtide_debug_log_v13('表單驗證失敗', wc_get_notices('error'));
        return; 
    }

    
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
    
    // 記錄購物車內容用於調試
    newtide_debug_log_v13('購物車內容', [
        'cart_items' => WC()->cart->get_cart(),
        'meta_key' => NEWTIDE_META_KEY_V13,
        'cart_contains_meta' => !empty($cart_item[NEWTIDE_META_KEY_V13])
    ]);
    
    // 將購物車項目加入訂單
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        // 確保數量是有效的正整數
        $quantity = absint($cart_item['quantity']);
        if ($quantity < 1) {
            $quantity = 1; // 確保至少為1
        }

        // 【核心修正】直接從 $_POST 讀取 schedule 的值
        $schedule_value = '';
        if (isset($_POST['cart'][$cart_item_key]['schedule'])) {
            $schedule_value = sanitize_text_field($_POST['cart'][$cart_item_key]['schedule']);
        }

        // 偵錯日誌：現在我們檢查從 POST 讀取到的值
        newtide_debug_log_v13('【讀取 POST】檢查提交表單中的 Schedule 值', [
            'cart_item_key' => $cart_item_key,
            'product_name'  => $cart_item['data']->get_name(),
            'schedule_value_from_post' => $schedule_value ?: '未設置或空值',
            'quantity' => $quantity
        ]);

        // 將產品加入訂單
        $product = $cart_item['data'];
        $item_id = $order->add_product($product, $quantity);

        // 如果成功從 POST 讀取到值，才執行儲存
        if ($item_id && !empty($schedule_value)) {

			// 為了讓其他功能（例如重新整理頁面時能記住選項）正常運作，
			// 我們順便把這個值更新回購物車 Session 中
			WC()->cart->cart_contents[$cart_item_key][NEWTIDE_META_KEY_V13] = $schedule_value;

			// 獲取剛剛建立的訂單項目物件
			$order_item = $order->get_item($item_id);

			if ($order_item) {
				// 使用常數來更新 Meta，確保一致性
				$order_item->update_meta_data(NEWTIDE_META_KEY_V13, $schedule_value);

				// 儲存這個訂單項目的變更
				$order_item->save();

				// 記錄調試信息，確認儲存動作已執行
				newtide_debug_log_v13('【儲存】已執行 update_meta_data', [
					'item_id'    => $item_id,
					'meta_key'   => NEWTIDE_META_KEY_V13,
					'meta_value' => $schedule_value
				]);
			}
		}
	}
	// 在迴圈結束後，手動更新一次購物車 session，以保存我們剛剛寫入的 schedule 值
	WC()->cart->set_session();
    
    // 計算訂單總額
    $order->calculate_totals();
    
    // 確保訂單總額正確反映項目總和
    $calculated_total = 0;
    foreach ($order->get_items() as $item) {
        $calculated_total += $item->get_total();
    }
    
    $order->set_total($calculated_total);
    
    // 保存訂單以確保所有變更都被寫入
    $order->save();
    
    // 記錄訂單保存後的項目元數據
    if (function_exists('wc_get_order')) {
        $saved_order = wc_get_order($order->get_id());
        if ($saved_order) {
            $items_data = [];
            foreach ($saved_order->get_items() as $item_id => $item) {
                $items_data[] = [
                    'item_id' => $item_id,
                    'product_id' => $item->get_product_id(),
                    'meta_data' => $item->get_meta_data()
                ];
            }
            newtide_debug_log_v13('訂單保存後的項目元數據', [
                'order_id' => $order->get_id(),
                'items' => $items_data
            ]);
        }
    }
    
    // 設定付款方式
    $order->set_payment_method('bacs');
    $order->set_payment_method_title(__('Bank Transfer', 'woocommerce'));
    
    // 設定訂單狀態並儲存
    $order->update_status('processing', __('Order created via custom inquiry form', 'woocommerce'));
    $order_id = $order->save();
    
    if (is_wp_error($order_id)) {
        newtide_debug_log_v13('訂單儲存失敗', $order_id->get_error_message());
        wc_add_notice(__('There was an error creating your order. Please try again.', 'woocommerce'), 'error');
        return;
    }
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

    WC()->cart->empty_cart();
    
    // 清除購物車
    WC()->cart->empty_cart();
    
    // 確保沒有錯誤訊息
    wc_clear_notices();
    
    // 跳轉到感謝頁面
    $thank_you_page_url = get_permalink(get_page_by_path('thankyou'));
    if (!$thank_you_page_url) {
        $thank_you_page_url = wc_get_checkout_url();
    }
    
    $redirect_url = add_query_arg('inquiry-received', $order_id, $thank_you_page_url);
    newtide_debug_log_v13('跳轉到感謝頁面', ['url' => $redirect_url]);
    
    wp_safe_redirect($redirect_url);
    exit;
}
