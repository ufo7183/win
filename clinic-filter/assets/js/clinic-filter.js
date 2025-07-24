jQuery(document).ready(function($){
    function filterClinics(city_id, area_id, keyword){
        $.ajax({
            url: clinicAjax.ajaxurl,
            method: "POST",
            data: {
                action: "clinic_filter",
                security: clinicAjax.nonce,
                city_id: city_id,
                area_id: area_id,
                keyword: keyword
            },
            beforeSend: function(){
                $("#clinic-list-container").addClass("loading");
            },
            success: function(res){
                $("#clinic-list-container").removeClass("loading").html(res);
            }
        });
    }

    // 區域
    $("#clinic_city").on("change", function(){
        var city_id = $(this).val() || "";
        $("#clinic_area").html("<option value=''>選擇縣市</option>").prop("disabled", true);

        // 篩選
        filterClinics(city_id, "", $("#clinic_keyword").val());
        if(!city_id) return;

        // 抓子區域
        $.ajax({
            url: clinicAjax.ajaxurl,
            method: "POST",
            data: {
                action: "clinic_filter_get_districts",
                security: clinicAjax.nonce,
                city_id: city_id
            },
            success: function(response){
                if(response.success && response.data.length > 0){
                    $("#clinic_area").prop("disabled", false);
                    $.each(response.data, function(i, item){
                        $("#clinic_area").append("<option value='"+item.term_id+"'>"+item.name+"</option>");
                    });
                } else {
                    $("#clinic_area").prop("disabled", true);
                }
            }
        });
    });

    // 縣市
    $("#clinic_area").on("change", function(){
        filterClinics($("#clinic_city").val(), $(this).val(), $("#clinic_keyword").val());
    });

    // 搜尋
    $("#clinic-filter-submit").on("click", function(e){
        e.preventDefault();
        filterClinics($("#clinic_city").val(), $("#clinic_area").val(), $("#clinic_keyword").val());
    });
});
jQuery(document).ready(function($){
    var offset = 25;
    var isLoading = false;

    function filterClinics(city_id, area_id, keyword, append = false){
        if(isLoading) return;
        isLoading = true;
        
        $.ajax({
            url: clinicAjax.ajaxurl,
            method: "POST",
            data: {
                action: "clinic_filter",
                security: clinicAjax.nonce,
                city_id: city_id,
                area_id: area_id,
                keyword: keyword,
                offset: append ? offset : 0
            },
            beforeSend: function(){
                $("#clinic-list-container").addClass("loading");
                if(!append) {
                    $("#clinic-list").html("");
                }
            },
            success: function(res){
                $("#clinic-list-container").removeClass("loading");

                if(res.success) {
                    // 如果不是 append，就重置
                    if(!append) {
                        $("#clinic-list-container").html("<div id='clinic-list'></div>");
                        $("#clinic-list").html(res.data.html);
                    } else {
                        $("#clinic-list").append(res.data.html);
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
                    $("#clinic-list-container").html("<div class='no-results'>查無結果</div>");
                    $("#load-more").addClass("hidden");
                }
                isLoading = false;
            }
        });
    }

    // 區域
    $("#clinic_city").on("change", function(){
        var city_id = $(this).val() || "";
        $("#clinic_area").html("<option value=''>選擇縣市</option>").prop("disabled", true);

        filterClinics(city_id, "", $("#clinic_keyword").val());

        if(!city_id) return;

        // 抓子區域
        $.ajax({
            url: clinicAjax.ajaxurl,
            method: "POST",
            data: {
                action: "clinic_filter_get_districts",
                security: clinicAjax.nonce,
                city_id: city_id
            },
            success: function(response){
                if(response.success && response.data.length > 0){
                    $("#clinic_area").prop("disabled", false);
                    $.each(response.data, function(i, item){
                        $("#clinic_area").append("<option value='" + item.term_id + "'>" + item.name + "</option>");
                    });
                } else {
                    $("#clinic_area").prop("disabled", true);
                }
            }
        });
    });

    // 縣市
    $("#clinic_area").on("change", function(){
        filterClinics($("#clinic_city").val(), $(this).val(), $("#clinic_keyword").val());
    });

    // 搜尋
    $("#clinic-filter-submit").on("click", function(e){
        e.preventDefault();
        filterClinics($("#clinic_city").val(), $("#clinic_area").val(), $("#clinic_keyword").val());
    });

    // Load More
    $(document).on("click", "#load-more", function(){
        filterClinics($("#clinic_city").val(), $("#clinic_area").val(), $("#clinic_keyword").val(), true);
    });
});
