jQuery(document).ready(function($) {
    var isLoading = false;
    var currentOffset = 0;
    var postsPerPage = 25; // 每次載入 25 筆
    var searchTimer = null;

    // 重置表單並顯示所有診所
    function resetForm() {
        $("#clinic-city").val('').trigger('change');
        $("#clinic-district").val('').prop('disabled', true);
        $("#clinic-keyword").val('');
        currentOffset = 0;
        // 觸發一次空搜尋以顯示所有診所
        filterClinics('', '', '');
    }

    // 篩選診所函數
    function filterClinics(city_id, area_id, keyword, append = false) {
        if (isLoading) return;
        isLoading = true;
        
        // 顯示載入中
        var $loadingElement = $("#clinic-loading");
        if (!append) {
            $loadingElement.show();
        }
        
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
                $loadingElement.hide();
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
        resetForm();
    });

    // 載入更多按鈕點擊事件
    $(document).on("click", "#load-more", function() {
        const cityId = $("#clinic-city").val() || '';
        const areaId = $("#clinic-district").val() || '';
        const keyword = $("#clinic-keyword").val().trim();
        filterClinics(cityId, areaId, keyword, true);
    });

    // 縣市變更事件
    $("#clinic-city").on("change", function() {
        const cityId = $(this).val();
        const $districtSelect = $("#clinic-district");
        
        $districtSelect.prop('disabled', true).html('<option value="">載入中...</option>');
        
        // 清除區域選擇並觸發篩選
        $districtSelect.val('').trigger('change');
        
        if (!cityId) {
            $districtSelect.prop('disabled', true).html('<option value="">選擇縣市</option>');
            currentOffset = 0;
            const keyword = $("#clinic-keyword").val().trim();
            filterClinics('', '', keyword);
            return;
        }
        
        // 取得該縣市下的區域
        $.ajax({
            url: clinicAjax.ajaxurl,
            method: "POST",
            data: {
                action: 'clinic_filter_get_districts',
                city_id: cityId,
                nonce: clinicAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $districtSelect.html(response.data).prop('disabled', false);
                    // 觸發區域變更以進行篩選
                    $districtSelect.trigger('change');
                } else {
                    console.error('獲取區域資料失敗');
                    $districtSelect.html('<option value="">載入失敗</option>').prop('disabled', true);
                    // 即使區域載入失敗，仍然觸發篩選
                    currentOffset = 0;
                    const keyword = $("#clinic-keyword").val().trim();
                    filterClinics(cityId, '', keyword);
                }
            },
            error: function() {
                console.error('AJAX 請求失敗');
                $districtSelect.html('<option value="">載入失敗</option>').prop('disabled', true);
                // 即使 AJAX 失敗，仍然觸發篩選
                currentOffset = 0;
                const keyword = $("#clinic-keyword").val().trim();
                filterClinics(cityId, '', keyword);
            }
        });
    });
    
    // 區域變更時觸發搜尋
    $("#clinic-district").on("change", function() {
        const cityId = $("#clinic-city").val() || '';
        const areaId = $(this).val() || '';
        const keyword = $("#clinic-keyword").val().trim();
        currentOffset = 0;
        filterClinics(cityId, areaId, keyword);
    });
    
    // 即時搜尋功能（移除搜尋按鈕）
    $("#clinic-keyword").on("input", function() {
        // 清除之前的計時器
        if (searchTimer) {
            clearTimeout(searchTimer);
        }
        
        // 設置新的計時器，延遲 300 毫秒後執行搜尋
        searchTimer = setTimeout(function() {
            const cityId = $("#clinic-city").val() || '';
            const areaId = $("#clinic-district").val() || '';
            const keyword = $("#clinic-keyword").val().trim();
            currentOffset = 0;
            filterClinics(cityId, areaId, keyword);
        }, 300);
    });
    
    // 初始載入時顯示所有診所
    resetForm();
});
