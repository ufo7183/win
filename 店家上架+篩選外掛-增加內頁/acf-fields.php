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
