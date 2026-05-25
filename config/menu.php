<?php

return [
    'ColorMatching' => [
        'title' => 'เทียบสี',
        'icon' => 'ti-receipt-tax',
        'route_name' => 'category.color_matching',
        'permission' => 'color_matching read',
    ],
    'Quotation' => [
        'title' => 'ใบเสนอราคา',
        'icon' => 'ti-receipt-tax',
        'route_name' => 'category.index',
        'permission' => 'quotation read',
    ],
    'Order' => [
        'title' => 'Order',
        'icon' => 'ti-receipt-tax',
        'route_name' => 'category.order',
        'permission' => 'order read',
    ],

    'Production' => [
        'title' => 'แผนการผลิต',
        'icon' => 'ti-server',
        'permission' => 'production read',
        'sub_menu' => [
            'ProductionOrder' => [
                'title' => 'Order สั่งผลิต',
                'icon' => '',
                'menu_parent' => 'Production',
                'route_name' => 'production.order.index',
                'permission' => 'production read',
            ],
            'ProductionPlanning' => [
                'title' => 'วางแผนการผลิต',
                'icon' => '',
                'menu_parent' => 'Production',
                'route_name' => 'production.planning',
                'permission' => 'productionplanning read',
            ],
        ],
    ],
    'Customer' => [
        'title' => 'ฐานข้อมูลลูกค้า',
        'icon' => 'ti-receipt-tax',
        'route_name' => 'category.customer',
        'permission' => 'customer read',
    ],
    'Report' => [
        'title' => 'รางยงาน',
        'icon' => 'ti-receipt-tax',
        'route_name' => 'category.report',
        'permission' => 'report read',
    ],
    'MasterSettings' => [
        'type'  => 'header',
        'title' => 'Settings',
    ],
    'Permission' => [
        'title' => 'สิทธิ์การใช้งาน',
        'icon' => 'ti-receipt-tax',
        'route_name' => 'category.permission',
        'permission' => 'permission read',
    ],


];
