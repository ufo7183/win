jQuery(function($) {
    // Function to reload the page to refresh the nonce
    function reloadWithNewNonce() {
        console.log('Reloading page to refresh nonce...');
        window.location.reload();
    }

    function fetchStores(page) {
        const city = jQuery('#store_city').val();
        const area = jQuery('#store_area').val();
        const industry = jQuery('#store_industry').val();
        const keyword = jQuery('#store_keyword').val();
        let perPage = 15;
        const perPageInput = document.getElementById('store_per_page');
        if (perPageInput) {
            perPage = parseInt(perPageInput.value, 10) || 15;
        }
        perPage = Math.max(1, Math.min(50, perPage));

        const resultsContainer = jQuery('#store-list-results');
        const paginationContainer = jQuery('#store-list-pagination');

        // Check if nonce exists
        if (!storeAjax || !storeAjax.nonce) {
            console.error('Security nonce is missing. Reloading page...');
            reloadWithNewNonce();
            return;
        }

        jQuery.ajax({
            url: storeAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'store_filter',
                nonce: storeAjax.nonce,
                paged: page,
                city: city,
                area: area,
                industry: industry,
                keyword: keyword,
                per_page: perPage
            },
            beforeSend: function() {
                resultsContainer.css('opacity', 0.5);
            },
            success: function(response) {
                resultsContainer.css('opacity', 1);
                
                // Check for nonce failure
                if (response.data && response.data === -1) {
                    console.error('Nonce verification failed. Reloading page...');
                    reloadWithNewNonce();
                    return;
                }
                
                if (response.success) {
                    resultsContainer.html(response.data.html);
                    paginationContainer.html(response.data.pagination);
                } else {
                    const errorMsg = response.data && response.data.message 
                        ? response.data.message 
                        : '查無結果或發生錯誤。';
                    resultsContainer.html('<p class="no-results">' + errorMsg + '</p>');
                    paginationContainer.html('');
                }
            },
            error: function(xhr, status, error) {
                resultsContainer.css('opacity', 1);
                
                // Try to parse the error response
                let errorMessage = '請求失敗，請稍後再試。';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.data && response.data.message) {
                        errorMessage = response.data.message;
                    }
                } catch (e) {
                    console.error('Error parsing error response:', e);
                }
                
                resultsContainer.html('<p class="no-results">' + errorMessage + '</p>');
                paginationContainer.html('');
                
                // If it's a 403 (Forbidden), likely a nonce issue
                if (xhr.status === 403) {
                    console.error('Access forbidden - possible nonce issue. Reloading page...');
                    setTimeout(reloadWithNewNonce, 2000);
                }
            }
        });
    }

    jQuery('#store-filter-form').on('submit', function(e) {
        e.preventDefault();
        fetchStores(1);
    });

    jQuery('#store-filter-form').on('change', 'select', function() {
        fetchStores(1);
    });

    jQuery('#store-list-pagination').on('click', 'a.page-numbers', function(e) {
        e.preventDefault();
        const href = jQuery(this).attr('href');
        let page = 1;
        const match = href.match(/paged=(\d+)/);
        if (match) page = parseInt(match[1], 10);
        fetchStores(page);
    });

    jQuery('#store_city').on('change', function() {
        const city_id = jQuery(this).val();
        const areaSelect = jQuery('#store_area');
        areaSelect.html('<option value="">選擇區域</option>').prop('disabled', true);
        if (!city_id) return;

        // Check if nonce exists
        if (!storeAjax || !storeAjax.nonce) {
            console.error('Security nonce is missing. Reloading page...');
            reloadWithNewNonce();
            return;
        }

        jQuery.ajax({
            url: storeAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'store_filter_get_districts',
                nonce: storeAjax.nonce,
                city_id: city_id
            },
            success: function(response) {
                // Check for nonce failure
                if (response.data && response.data === -1) {
                    console.error('Nonce verification failed. Reloading page...');
                    reloadWithNewNonce();
                    return;
                }
                
                if (response.success) {
                    areaSelect.prop('disabled', false);
                    areaSelect.empty().append('<option value="">選擇區域</option>');
                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function(item) {
                            areaSelect.append('<option value="' + item.term_id + '">' + item.name + '</option>');
                        });
                    }
                } else {
                    console.error('Error fetching districts:', response.data && response.data.message || 'Unknown error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                // If it's a 403 (Forbidden), likely a nonce issue
                if (xhr.status === 403) {
                    console.error('Access forbidden - possible nonce issue. Reloading page...');
                    setTimeout(reloadWithNewNonce, 2000);
                }
            }
        });
    });

    fetchStores(1);
});