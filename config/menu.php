<?php

return [
    'ColorMatching' => [
        'title' => 'เทียบสี',
        'icon' => 'ti-receipt-tax',
        'route_name' => 'color_matching.index',
        'permission' => 'color_matching read',
    ],
    'Quotation' => [
        'title' => 'ใบเสนอราคา',
        'icon' => 'ti-receipt-tax',
        'route_name' => 'quotation.index',
        'permission' => 'quotation read',
    ],
    'Order' => [
        'title' => 'Order',
        'icon' => 'ti-receipt-tax',
        'route_name' => 'order.index',
        'permission' => 'order read',
    ],

    'Production' => [
        'title' => 'แผนการผลิต',
        'icon' => 'ti-server',
        'permission' => 'production read',
        'sub_menu' => [
            'ProductionOrder' => [
                'title' => 'Sale Order',
                'icon' => '',
                'menu_parent' => 'Production',
                'route_name' => 'production.order.index',
                'permission' => 'production read',
            ],
            'ProductionPlanning' => [
                'title' => 'วางแผนการผลิต',
                'icon' => '',
                'menu_parent' => 'Production',
                'route_name' => 'production.planning.index',
                'permission' => 'productionplanning read',
            ],
        ],
    ],
    'Customer' => [
        'title' => 'ฐานข้อมูลลูกค้า',
        'icon' => 'ti-receipt-tax',
        'route_name' => 'customer.index',
        'permission' => 'customer read',
    ],
    'Report' => [
        'title' => 'รายงาน',
        'icon' => 'ti-receipt-tax',
        'route_name' => 'report.index',
        'permission' => 'report read',
    ],
    'MasterSettings' => [
        'type'  => 'header',
        'title' => 'Settings',
    ],
    'Permission' => [
        'title' => 'สิทธิ์การใช้งาน',
        'icon' => 'ti-receipt-tax',
        'route_name' => 'permission.index',
        'permission' => 'permission read',
    ],


];
