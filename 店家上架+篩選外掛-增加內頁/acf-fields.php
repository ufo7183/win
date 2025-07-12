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
