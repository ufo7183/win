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
            'key' => 'field_store_google_map_url',
            'label' => '店家地址 Google Map 網址',
            'name' => 'store_google_map_url',
            'type' => 'url',
        ),
        array(
            'key' => 'field_store_line_url',
            'label' => 'LINE 網址',
            'name' => 'store_line_url',
            'type' => 'url',
        ),
        array(
            'key' => 'field_store_ig_url',
            'label' => 'Instagram 網址',
            'name' => 'store_ig_url',
            'type' => 'url',
        ),
        array(
            'key' => 'field_store_fb_url',
            'label' => 'Facebook 網址',
            'name' => 'store_fb_url',
            'type' => 'url',
        ),
        array(
            'key' => 'field_store_business_hours',
            'label' => '營業時間',
            'name' => 'store_business_hours',
            'type' => 'wysiwyg',
            'instructions' => '請使用 WYSIWYG 編輯器輸入營業時間',
            'media_upload' => 0,
            'tabs' => 'all',
            'toolbar' => 'full',
            'default_value' => '',
        ),
        array(
            'key' => 'field_store_info',
            'label' => '店家資訊',
            'name' => 'store_info',
            'type' => 'wysiwyg',
            'instructions' => '請使用 WYSIWYG 編輯器輸入店家詳細資訊',
            'media_upload' => 0,
            'tabs' => 'all',
            'toolbar' => 'full',
            'default_value' => '',
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
));

endif;
