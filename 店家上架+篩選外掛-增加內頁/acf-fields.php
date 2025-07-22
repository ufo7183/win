<?php
if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
    'key' => 'group_store_details',
    'title' => '店家詳細資訊',
    'fields' => array(
        array(
            'key' => 'field_store_address',
            'label' => '店家地址',
            'name' => 'store_address',
            'type' => 'text',
        ),
        array(
            'key' => 'field_store_phone',
            'label' => '店家電話',
            'name' => 'store_phone',
            'type' => 'text',
        ),
        array(
            'key' => 'field_store_contact_person',
            'label' => '聯絡人',
            'name' => 'store_contact_person',
            'type' => 'text',
        ),
        array(
            'key' => 'field_store_map_embed',
            'label' => '地圖嵌入代碼',
            'name' => 'store_map_embed',
            'type' => 'textarea',
            'instructions' => '請在 Google Maps 中找到店家位置，點擊分享按鈕，選擇「嵌入地圖」，複製 iframe 代碼並貼在這裡。',
            'rows' => 4,
            'placeholder' => '<iframe src="https://www.google.com/maps/embed?..." width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>',
        ),
        array(
            'key' => 'field_store_business_hours',
            'label' => '營業時間',
            'name' => 'store_business_hours',
            'type' => 'wysiwyg',
            'tabs' => 'all',
            'toolbar' => 'full',
            'media_upload' => 1,
        ),
        array(
            'key' => 'field_store_information',
            'label' => '店家資訊',
            'name' => 'store_information',
            'type' => 'wysiwyg',
            'tabs' => 'all',
            'toolbar' => 'full',
            'media_upload' => 1,
        ),
        array(
            'key' => 'field_facebook_url',
            'label' => 'Facebook 網址',
            'name' => 'facebook_url',
            'type' => 'url',
            'instructions' => '請輸入 Facebook 專頁或個人檔案的完整網址',
            'placeholder' => 'https://www.facebook.com/yourpage',
        ),
        array(
            'key' => 'field_instagram_url',
            'label' => 'Instagram 網址',
            'name' => 'instagram_url',
            'type' => 'url',
            'instructions' => '請輸入 Instagram 個人檔案的完整網址',
            'placeholder' => 'https://www.instagram.com/yourusername',
        ),
        array(
            'key' => 'field_line_url',
            'label' => 'LINE 官方帳號網址',
            'name' => 'line_url',
            'type' => 'url',
            'instructions' => '請輸入 LINE 官方帳號的網址',
            'placeholder' => 'https://lin.ee/yourlineid',
        ),
    ),
    'location' => array(
        array(
            array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'store',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => true,
    'description' => '',
));

endif;
