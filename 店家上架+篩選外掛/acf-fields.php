<?php
if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
    'key' => 'group_store_details',
    'title' => '店家詳細資訊',
    'fields' => array(
        array(
            'key' => 'field_store_image',
            'label' => '店家照片',
            'name' => 'store_image',
            'type' => 'image',
            'instructions' => '上傳一張代表店家的照片。',
            'return_format' => 'array', // 可選 'array', 'url', 'id'
            'preview_size' => 'medium',
            'library' => 'all',
        ),
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
            'key' => 'field_store_opening_hours',
            'label' => '營業時間',
            'name' => 'store_opening_hours',
            'type' => 'textarea',
        ),
        array(
            'key' => 'field_store_website',
            'label' => '店家網址',
            'name' => 'store_website',
            'type' => 'url',
        ),
        array(
            'key' => 'field_store_facebook',
            'label' => 'Facebook',
            'name' => 'store_facebook',
            'type' => 'url',
        ),
        array(
            'key' => 'field_store_instagram',
            'label' => 'Instagram',
            'name' => 'store_instagram',
            'type' => 'url',
        ),
        array(
            'key' => 'field_store_line',
            'label' => 'Line',
            'name' => 'store_line',
            'type' => 'url',
        ),
        array(
            'key' => 'field_store_extra_info',
            'label' => '額外資訊',
            'name' => 'store_extra_info',
            'type' => 'textarea',
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
