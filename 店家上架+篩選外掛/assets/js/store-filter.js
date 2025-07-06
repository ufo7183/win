jQuery(function($) {

    /**
     * Main function to fetch stores via AJAX.
     * @param {number} page The page number to fetch.
     */
    function fetchStores(page) {
        const city = $('#store_city').val();
        const area = $('#store_area').val();
        const keyword = $('#store_keyword').val();
        const resultsContainer = $('#store-list-results');
        const paginationContainer = $('#store-list-pagination');

        $.ajax({
            url: storeAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'store_filter',
                nonce: storeAjax.nonce,
                paged: page,
                city: city,
                area: area,
                keyword: keyword
            },
            beforeSend: function() {
                // You can add a loading spinner or overlay here
                resultsContainer.css('opacity', 0.5);
            },
            success: function(response) {
                resultsContainer.css('opacity', 1);
                if (response.success) {
                    resultsContainer.html(response.data.html);
                    paginationContainer.html(response.data.pagination);
                } else {
                    resultsContainer.html('<p class="no-results">查無結果或發生錯誤。</p>');
                    paginationContainer.html('');
                }
            },
            error: function() {
                resultsContainer.css('opacity', 1);
                resultsContainer.html('<p class="no-results">請求失敗，請稍後再試。</p>');
                paginationContainer.html('');
            }
        });
    }

    // --- EVENT HANDLERS ---

    // 1. Handle filter form submission
    $('#store-filter-form').on('submit', function(e) {
        e.preventDefault();
        fetchStores(1); // On any new filter, go back to page 1
    });

    // 2. Handle pagination clicks (uses event delegation)
    $('#store-list-pagination').on('click', 'a.page-numbers', function(e) {
        e.preventDefault();
        const href = $(this).attr('href');
        let page = 1;

        // Extract page number from URL (e.g., ?paged=2)
        const match = href.match(/paged=(\d+)/);
        if (match) {
            page = parseInt(match[1], 10);
        }

        fetchStores(page);
    });

    // 3. Handle city change to populate areas
    $('#store_city').on('change', function() {
        const city_id = $(this).val();
        const areaSelect = $('#store_area');

        areaSelect.html('<option value="">選擇區域</option>').prop('disabled', true);

        if (!city_id) {
            return; // Do nothing if 'Select City' is chosen
        }

        $.ajax({
            url: storeAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'store_filter_get_districts',
                nonce: storeAjax.nonce, // Note: The PHP handler uses 'store_filter_nonce'
                city_id: city_id
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    areaSelect.prop('disabled', false);
                    $.each(response.data, function(i, item) {
                        areaSelect.append(new Option(item.name, item.term_id));
                    });
                } 
            }
        });
    });

    // --- INITIAL LOAD ---
    fetchStores(1);

});
