// 使用 jQuery 的 document.ready() 來確保所有 HTML 元素都已經載入完成，才執行我們的程式碼。
jQuery(document).ready(function($) {
    var urlParams = new URLSearchParams(window.location.search);
    // 清除登出相關的 URL 參數
    if (urlParams.has('loggedout')) {
        var newUrl = window.location.pathname;
        if (urlParams.has('category')) {
            newUrl += '?category=' + urlParams.get('category');
        }
        window.history.replaceState({}, document.title, newUrl);
    }
    
    // 頁面載入時檢查 URL 參數並設置對應的標籤
    var categoryParam = urlParams.get('category');
    if (categoryParam) {
        var $targetTab = $('.yat-custom-tabs-container a[data-term-id="' + categoryParam + '"]');
        if ($targetTab.length) {
            $targetTab.parent('li').addClass('yat-active').siblings().removeClass('yat-active');
            if (typeof sessionStorage !== 'undefined') {
                sessionStorage.setItem('current_tab_term_id', categoryParam);
            }
        }
    } else if (typeof sessionStorage !== 'undefined' && sessionStorage.getItem('current_tab_term_id')) {
        // 如果 URL 沒有 category 參數，但有保存的 tab 狀態，則恢復
        var savedTabId = sessionStorage.getItem('current_tab_term_id');
        var $savedTab = $('.yat-custom-tabs-container a[data-term-id="' + savedTabId + '"]');
        if ($savedTab.length) {
            $savedTab.parent('li').addClass('yat-active').siblings().removeClass('yat-active');
        }
    }

    // 選取所有在 '.yat-custom-tabs-container' 容器內的 'a' 連結，並綁定點擊事件。
    $('.yat-custom-tabs-container').on('click', 'a', function(e) {
        // 阻止連結的預設跳轉行為。
        e.preventDefault();
        
        var $this = $(this);
        var $parentLi = $this.parent('li');
        
        // 如果點擊的是當前已選中的標籤，則不執行任何操作
        if ($parentLi.hasClass('yat-active')) {
            return false;
        }
        
        // 更新活動標籤狀態
        $parentLi.siblings().removeClass('yat-active');
        $parentLi.addClass('yat-active');
        
        // 網站完全公開，無需登入檢查

        // --- 資料準備 ---
        var termId = $this.data('term-id'); // 讀取 `<a>` 元素上的 `data-term-id` 屬性值。
        var container = $this.closest('.yat-custom-tabs-container'); // 找到父層容器 <ul>。
        var queryId = container.data('query-id'); // 從容器讀取 `data-query-id` 屬性值。
        var taxonomy = container.data('taxonomy'); // 從容器讀取 data-taxonomy 屬性
        var $loopGrid = $('#' + queryId);
        
        // 添加加載狀態
        $loopGrid.addClass('ajax-loading tab-loading');
        
        // 禁用所有標籤點擊，防止重複點擊
        container.find('a').css('pointer-events', 'none');
        
        // 更新 URL 狀態
        var newUrl = window.location.pathname;
        if (termId !== 'all') {
            newUrl += '?category=' + termId;
        } else {
            // 如果是全部標籤，確保移除 category 參數
            newUrl = window.location.pathname;
        }
        window.history.pushState({ termId: termId }, '', newUrl);
        
        // 保存當前選中的標籤到 sessionStorage
        if (typeof sessionStorage !== 'undefined') {
            sessionStorage.setItem('current_tab_term_id', termId);
        }
        
        // 記錄開始時間用於性能監控
        var startTime = new Date().getTime();
        
        // --- 先更新 Session ---
        $.ajax({
            url: your_ajax_obj.ajax_url, 
            type: 'POST', 
            data: {
                action: 'your_filter_action', 
                nonce: your_ajax_obj ? your_ajax_obj.nonce : '', 
                term_id: termId, 
                taxonomy: taxonomy,
                query_id: queryId,
                timestamp: new Date().getTime(), // 防止緩存
                is_public: '1' // 標記為公開請求
            },
            success: function(response) {
                // 計算請求耗時
                var endTime = new Date().getTime();
                var loadTime = endTime - startTime;
                
                // 除錯監控
                console.log("--- Session 更新成功 ---");
                console.log("請求耗時: " + loadTime + "ms");
                
                if (response.data) {
                    console.log("[DEBUG] 服務器消息: " + response.data.message);
                    console.log("[DEBUG] 當前分類ID: " + response.data.session_term_id);
                    console.log("[DEBUG] 當前分類法: " + response.data.session_taxonomy);
                }

                if (response.success) {
                    // 更新活動標籤 - 使用點擊的標籤來確保一致性
                    container.find('li').removeClass('yat-active');
                    $parentLi.addClass('yat-active');
                    
                    // 保存當前選中的標籤到 sessionStorage
                    if (typeof sessionStorage !== 'undefined') {
                        sessionStorage.setItem('current_tab_term_id', termId);
                    }
                    
                    // 使用 Elementor Pro 的 AJAX 重新載入功能
                    if (typeof elementorProFrontend !== 'undefined' && elementorProFrontend.modules && elementorProFrontend.modules.loop) {
                        console.log("觸發 Elementor Loop 重新載入...");
                        
                        // 觸發 Elementor Pro 的 AJAX 重新載入
                        elementorProFrontend.modules.loop.request({
                            widget_id: queryId,
                            page: 1,
                            filters: {
                                'taxonomy': taxonomy,
                                'term_id': termId === 'all' ? '' : termId,
                                'is_ajax': '1' // 添加標記表示這是 AJAX 請求
                            }
                        }).then(function() {
                            console.log("Loop 重新載入完成");
                            
                            // 滾動到網格頂部，平滑滾動
                            $('html, body').animate({
                                scrollTop: $loopGrid.offset().top - 100
                            }, 300);
                            
                            // 觸發視窗大小改變事件，確保元素正確重排
                            $(window).trigger('resize');
                        }).catch(function(error) {
                            console.error("Loop 重新載入失敗:", error);
                        });
                    } else {
                        console.error("Elementor Pro 的 Loop 模組未正確載入");
                        // 回退到傳統的刷新方式
                        location.reload();
                    }
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX 錯誤: ' + textStatus, errorThrown);
                // 顯示錯誤消息
                alert('更新分類時出錯，請稍後再試。');
            },
            complete: function() {
                // 無論成功或失敗，都移除加載狀態
                setTimeout(function() {
                    $loopGrid.removeClass('tab-loading');
                    // 重新啟用標籤點擊
                    container.find('a').css('pointer-events', 'auto');
                    console.log("請求完成，加載狀態已重置");
                }, 300); // 稍微延遲一下，讓過渡動畫看起來更自然
            }
        });
        
        return false;
    });
    
    // 初始化控制台提示
    console.log('團隊頁面標籤切換腳本已加載');
});