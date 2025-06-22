// 使用 jQuery 的 document.ready() 來確保所有 HTML 元素都已經載入完成，才執行我們的程式碼。
jQuery(document).ready(function($) {
    // 選取所有在 '.your-ajax-tabs-container' 容器內的 'a' 連結，並綁定點擊事件。
    $('.your-ajax-tabs-container').on('click', 'a', function(e) {
        // 阻止連結的預設跳轉行為。
        e.preventDefault();
        
        var $this = $(this);
        
        // 如果點擊的是當前已選中的標籤，則不執行任何操作
        if ($this.parent('li').hasClass('active')) {
            return false;
        }

        // --- 資料準備 ---
        var termId = $this.data('term-id'); // 讀取 `<a>` 元素上的 `data-term-id` 屬性值。
        var container = $this.closest('.your-ajax-tabs-container'); // 找到父層容器 <ul>。
        var queryId = container.data('query-id'); // 從容器讀取 `data-query-id` 屬性值。
        var taxonomy = container.data('taxonomy'); // 從容器讀取 data-taxonomy 屬性
        var $loopGrid = $('#' + queryId);
        
        // 添加加載狀態
        $loopGrid.addClass('ajax-loading tab-loading');
        
        // 禁用所有標籤點擊，防止重複點擊
        container.find('a').css('pointer-events', 'none');
        
        // 記錄開始時間用於性能監控
        var startTime = new Date().getTime();
        
        // --- 先更新 Session ---
        $.ajax({
            url: your_ajax_obj.ajax_url, 
            type: 'POST', 
            data: {
                action: 'your_filter_action', 
                nonce: your_ajax_obj.nonce, 
                term_id: termId, 
                taxonomy: taxonomy,
                query_id: queryId,
                timestamp: new Date().getTime() // 防止緩存
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
                    // 更新活動標籤
                    container.find('li').removeClass('active');
                    $this.parent('li').addClass('active');
                    
                    // 使用 Elementor Pro 的 AJAX 重新載入功能
                    if (typeof elementorProFrontend !== 'undefined' && elementorProFrontend.modules && elementorProFrontend.modules.loop) {
                        console.log("觸發 Elementor Loop 重新載入...");
                        
                        // 觸發 Elementor Pro 的 AJAX 重新載入
                        elementorProFrontend.modules.loop.request({
                            widget_id: queryId,
                            page: 1,
                            filters: {
                                'taxonomy': taxonomy,
                                'term_id': termId === 'all' ? '' : termId
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