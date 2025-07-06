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
