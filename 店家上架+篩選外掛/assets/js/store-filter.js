jQuery(document).ready(function($){
    function filterstores(city_id, area_id, keyword){
        $.ajax({
            url: storeAjax.ajaxurl,
            method: "POST",
            data: {
                action: "store_filter",
                security: storeAjax.nonce,
                city_id: city_id,
                area_id: area_id,
                keyword: keyword
            },
            beforeSend: function(){
                $("#store-list-container").addClass("loading");
            },
            success: function(res){
                $("#store-list-container").removeClass("loading").html(res);
            }
        });
    }

    // 縣市
    $("#store_city").on("change", function(){
        var city_id = $(this).val() || "";
        $("#store_area").html("<option value=''>選擇區域</option>").prop("disabled", true);

        // 篩選
        filterstores(city_id, "", $("#store_keyword").val());
        if(!city_id) return;

        // 抓子區域
        $.ajax({
            url: storeAjax.ajaxurl,
            method: "POST",
            data: {
                action: "store_filter_get_districts",
                security: storeAjax.nonce,
                city_id: city_id
            },
            success: function(response){
                if(response.success && response.data.length > 0){
                    $("#store_area").prop("disabled", false);
                    $.each(response.data, function(i, item){
                        $("#store_area").append("<option value='"+item.term_id+"'>"+item.name+"</option>");
                    });
                } else {
                    $("#store_area").prop("disabled", true);
                }
            }
        });
    });

    // 區域
    $("#store_area").on("change", function(){
        filterstores($("#store_city").val(), $(this).val(), $("#store_keyword").val());
    });

    // 搜尋
    $("#store-filter-submit").on("click", function(e){
        e.preventDefault();
        filterstores($("#store_city").val(), $("#store_area").val(), $("#store_keyword").val());
    });
});
jQuery(document).ready(function($){
    var offset = 25;
    var isLoading = false;

    function filterstores(city_id, area_id, keyword, append = false){
        if(isLoading) return;
        isLoading = true;
        
        $.ajax({
            url: storeAjax.ajaxurl,
            method: "POST",
            data: {
                action: "store_filter",
                security: storeAjax.nonce,
                city_id: city_id,
                area_id: area_id,
                keyword: keyword,
                offset: append ? offset : 0
            },
            beforeSend: function(){
                $("#store-list-container").addClass("loading");
                if(!append) {
                    $("#store-list").html("");
                }
            },
            success: function(res){
                $("#store-list-container").removeClass("loading");

                if(res.success) {
                    // 如果不是 append，就重置
                    if(!append) {
                        $("#store-list-container").html("<div id='store-list'></div>");
                        $("#store-list").html(res.data.html);
                    } else {
                        $("#store-list").append(res.data.html);
                    }

                    // 更新 offset
                    offset = append ? offset + 25 : 25;

                    // 如果還有更多
                    if(res.data.total > offset) {
                        $("#load-more").removeClass("hidden");
                    } else {
                        $("#load-more").addClass("hidden");
                    }
                } else {
                    $("#store-list-container").html("<div class='no-results'>查無結果</div>");
                    $("#load-more").addClass("hidden");
                }
                isLoading = false;
            }
        });
    }

    // 縣市
    $("#store_city").on("change", function(){
        var city_id = $(this).val() || "";
        $("#store_area").html("<option value=''>選擇區域</option>").prop("disabled", true);

        filterstores(city_id, "", $("#store_keyword").val());

        if(!city_id) return;

        // 抓子區域
        $.ajax({
            url: storeAjax.ajaxurl,
            method: "POST",
            data: {
                action: "store_filter_get_districts",
                security: storeAjax.nonce,
                city_id: city_id
            },
            success: function(response){
                if(response.success && response.data.length > 0){
                    $("#store_area").prop("disabled", false);
                    $.each(response.data, function(i, item){
                        $("#store_area").append("<option value='" + item.term_id + "'>" + item.name + "</option>");
                    });
                } else {
                    $("#store_area").prop("disabled", true);
                }
            }
        });
    });

    // 區域
    $("#store_area").on("change", function(){
        filterstores($("#store_city").val(), $(this).val(), $("#store_keyword").val());
    });

    // 搜尋
    $("#store-filter-submit").on("click", function(e){
        e.preventDefault();
        filterstores($("#store_city").val(), $("#store_area").val(), $("#store_keyword").val());
    });

    // Load More
    $(document).on("click", "#load-more", function(){
        filterstores($("#store_city").val(), $("#store_area").val(), $("#store_keyword").val(), true);
    });
});
