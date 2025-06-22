/**
 * Elementor Loop Grid – 與 teams 標題同名分類的文章
 * --------------------------------------------------
 * 使用方法：
 * 1. 在 Elementor 的「Loop Grid / Posts」或「Loop Carousel」小工具
 *    → 進階 → 查詢 ID 填入： team_related_posts
 * 2. 佈景需有 single-teams.php 渲染 teams 單篇
 * 3. 進入任何單篇 teams 時，Loop Grid / Carousel 就會自動只顯示
 *    「分類名稱(不包含醫師二字) == 這篇 teams 標題」的文章（post）
 */
add_action( 'elementor/query/team_related_posts', 'filter_team_related_posts_query', 10, 2 );

function filter_team_related_posts_query( $query, $widget ) {
    // 只在 single teams 時才生效
    if ( ! is_singular( 'teams' ) ) {
        return;
    }

    global $post;
    if ( ! $post ) {
        return;
    }

    // 獲取團隊成員標題，並移除「醫師」字樣
    $team_title = str_replace('醫師', '', $post->post_title);
    $team_title = trim($team_title);

    if (empty($team_title)) {
        return;
    }

    // 獲取所有分類
    $categories = get_terms([
        'taxonomy' => 'category',
        'hide_empty' => false,
    ]);

    if (is_wp_error($categories) || empty($categories)) {
        return;
    }

    // 尋找名稱匹配的分類（不區分大小寫）
    $matching_terms = [];
    foreach ($categories as $category) {
        // 移除分類名稱中的「醫師」字樣進行比對
        $clean_category_name = str_replace('醫師', '', $category->name);
        $clean_category_name = trim($clean_category_name);
        
        if (strcasecmp($clean_category_name, $team_title) === 0) {
            $matching_terms[] = $category->term_id;
        }
    }

    if (empty($matching_terms)) {
        // 如果沒有找到匹配的分類，設置查詢返回空結果
        $query->set('post__in', [0]);
        return;
    }

    // 設定分類查詢
    $tax_query = [
        [
            'taxonomy' => 'category',
            'field'    => 'term_id',
            'terms'    => $matching_terms,
            'operator' => 'IN',
        ]
    ];

    // 確保 post_type 為 'post'
    $query->set('post_type', 'post');
    $query->set('posts_per_page', 12); // 設定每頁顯示數量
    $query->set('orderby', 'date');    // 依日期排序
    $query->set('order', 'DESC');      // 降序（最新在前）
    $query->set('tax_query', $tax_query);
}
