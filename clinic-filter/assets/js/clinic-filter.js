jQuery(document).ready(function($) {
    var isLoading = false;
    var currentOffset = 0;
    var postsPerPage = 25; // 每次載入 25 筆
    var searchTimer = null;

    // 載入指定地區的縣市
    function loadCitiesByRegion(regionId = '') {
        const $citySelect = $("#clinic-city");
        const defaultOption = '<option value="">選擇縣市</option>';
        
        $citySelect.html(defaultOption).prop('disabled', true);
        
        if (!regionId) {
            // 如果沒有選擇地區，載入所有縣市
            loadAllCities();
            return;
        }
        
        $.ajax({
            url: clinicAjax.ajaxurl,
            method: "POST",
            data: {
                action: 'clinic_filter_get_districts',
                city_id: regionId,
                nonce: clinicAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $citySelect.html(defaultOption + response.data);
                } else {
                    console.error('獲取縣市資料失敗');
                    $citySelect.html(defaultOption + '<option value="">載入失敗</option>');
                }
            },
            error: function() {
                console.error('AJAX 請求失敗');
                $citySelect.html(defaultOption + '<option value="">載入失敗</option>');
            },
            complete: function() {
                $citySelect.prop('disabled', false);
            }
        });
    }
    
    // 載入所有縣市
    function loadAllCities() {
        const $citySelect = $("#clinic-city");
        const defaultOption = '<option value="">選擇縣市</option>';
        
        $citySelect.html(defaultOption).prop('disabled', true);
        
        $.ajax({
            url: clinicAjax.ajaxurl,
            method: "POST",
            data: {
                action: 'clinic_filter_get_districts',
                city_id: 'all',
                nonce: clinicAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $citySelect.html(defaultOption + response.data);
                } else {
                    console.error('獲取縣市資料失敗');
                    $citySelect.html(defaultOption + '<option value="">載入失敗</option>');
                }
            },
            error: function() {
                console.error('AJAX 請求失敗');
                $citySelect.html(defaultOption + '<option value="">載入失敗</option>');
            },
            complete: function() {
                $citySelect.prop('disabled', false);
            }
        });
    }

    // 篩選診所函數
    function filterClinics(city_id, area_id, keyword, append = false) {
        if (isLoading) return;
        isLoading = true;
        
        // 確保 city_id 和 area_id 是數字或空字串
        city_id = city_id || '';
        area_id = area_id || '';
        keyword = keyword || '';
        
        $.ajax({
            url: clinicAjax.ajaxurl,
            method: "POST",
            data: {
                action: 'clinic_filter',
                city_id: city_id,
                area_id: area_id,
                keyword: keyword,
                offset: append ? currentOffset : 0,
                nonce: clinicAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (response.data && response.data.data) {
                        if (append) {
                            $("#clinic-list-container .row").append(response.data.data);
                            currentOffset += postsPerPage;
                        } else {
                            $("#clinic-list-container").html(response.data.data);
                            currentOffset = postsPerPage;
                        }
                        // 更新載入更多按鈕狀態
                        updateLoadMoreButton(response.data.has_more);
                    } else {
                        $("#clinic-list-container").html('<div class="alert alert-info">此地區暫無認證診所</div>');
                        $("#load-more").hide();
                    }
                } else {
                    console.error('伺服器回應錯誤:', response);
                    $("#clinic-list-container").html('<div class="alert alert-info">搜尋不到此診所</div>');
                    $("#load-more").hide();
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                $("#clinic-list-container").html('<div class="alert alert-info">搜尋不到此診所</div>');
                $("#load-more").hide();
            },
            complete: function() {
                isLoading = false;
            }
        });
    }

    // 更新載入更多按鈕狀態
    function updateLoadMoreButton(hasMore) {
        if (hasMore) {
            $("#load-more").show();
        } else {
            $("#load-more").hide();
        }
    }

    // 全部診所按鈕點擊事件
    $(document).on("click", "#reset-filters", function(e) {
        e.preventDefault();
        $("#clinic-region, #clinic-city").val('');
        loadAllCities();
        filterClinics('', '', '');
    });

    // 載入更多按鈕點擊事件
    $(document).on("click", "#load-more", function() {
        const regionId = $("#clinic-region").val() || '';
        const cityId = $("#clinic-city").val() || '';
        const keyword = $("#clinic-keyword").val().trim();
        filterClinics(cityId, regionId, keyword, true);
    });

    // 地區變更事件
    $("#clinic-region").on("change", function() {
        const regionId = $(this).val() || '';
        const cityId = $("#clinic-city").val() || '';
        const keyword = $("#clinic-keyword").val().trim();
        
        // 載入該地區的縣市
        loadCitiesByRegion(regionId);
        
        // 觸發篩選
        currentOffset = 0;
        filterClinics('', regionId, keyword);
    });
    
    // 縣市變更時觸發搜尋
    $("#clinic-city").on("change", function() {
        const regionId = $("#clinic-region").val() || '';
        const cityId = $(this).val() || '';
        const keyword = $("#clinic-keyword").val().trim();
        currentOffset = 0;
        filterClinics(cityId, regionId, keyword);
    });
    
    // 即時搜尋功能
    $("#clinic-keyword").on("input", function() {
        // 清除之前的計時器
        if (searchTimer) {
            clearTimeout(searchTimer);
        }
        
        // 設置新的計時器，延遲 300 毫秒後執行搜尋
        searchTimer = setTimeout(function() {
            const regionId = $("#clinic-region").val() || '';
            const cityId = $("#clinic-city").val() || '';
            const keyword = $("#clinic-keyword").val().trim();
            currentOffset = 0;
            filterClinics(cityId, regionId, keyword);
        }, 300);
    });
    
    // 初始載入時顯示所有縣市
    loadAllCities();
});
