<?php

return [
    'ColorMatching' => [
        'title' => 'เทียบสี',
        'icon' => 'ti-palette',
        'route_name' => 'color_matching.index',
        'permission' => 'color_matching read',
    ],
    'Quotation' => [
        'title' => 'ใบเสนอราคา',
        'icon' => 'ti-file-invoice',
        'route_name' => 'quotation.index',
        'permission' => 'quotation read',
    ],
    'Order' => [
        'title' => 'Order',
        'icon' => 'ti-shopping-cart',
        'route_name' => 'order.index',
        'permission' => 'order read',
    ],

    'Production' => [
        'title' => 'แผนการผลิต',
        'icon' => 'ti-calendar-stats',
        'permission' => 'production read',
        'sub_menu' => [
            'ProductionOrder' => [
                'title' => 'Sale Order',
                'icon' => '',
                'menu_parent' => 'Production',
                'route_name' => 'production.order.index',
                'permission' => 'production read',
            ],
            'ProductionOrderPlan' => [
                'title' => 'แผนการผลิต Order',
                'icon' => '',
                'menu_parent' => 'Production',
                'route_name' => 'production.orderplan.index',
                'permission' => 'productionplanning read',
            ],
            'SemiPigment' => [
                'title' => 'อนุมัติ Semi',
                'icon' => '',
                'menu_parent' => 'Production',
                'route_name' => 'production.semipigment.index',
                'permission' => 'productionplanning read',
            ],
            'PigmentApproval' => [
                'title' => 'อนุมัติ Pigment',
                'icon' => '',
                'menu_parent' => 'Production',
                'route_name' => 'production.pigment.index',
                'permission' => 'productionplanning read',
            ],
            'ProductionPlanning' => [
                'title' => 'วางแผนการผลิต',
                'icon' => '',
                'menu_parent' => 'Production',
                'route_name' => 'production.planning.index',
                'permission' => 'productionplanning read',
            ],
            'PlanningStatus' => [
                'title' => 'สถานะ Planning',
                'icon' => '',
                'menu_parent' => 'Production',
                'route_name' => 'production.planningstatus.index',
                'permission' => 'productionplanning read',
            ],
        ],
    ],
    'Customer' => [
        'title' => 'ฐานข้อมูลลูกค้า',
        'icon' => 'ti-address-book',
        'route_name' => 'customer.index',
        'permission' => 'customer read',
    ],
    'Report' => [
        'title' => 'รายงาน',
        'icon' => 'ti-report',
        'route_name' => 'report.index',
        'permission' => 'report read',
    ],
    'MasterSettings' => [
        'type'  => 'header',
        'title' => 'Settings',
    ],
    'Permission' => [
        'title' => 'สิทธิ์การใช้งาน',
        'icon' => 'ti-shield-lock',
        'route_name' => 'permission.index',
        'permission' => 'permission read',
    ],
    'LinkUser' => [
        'title' => 'User',
        'icon' => 'ti-id-badge',
        'url' => 'user', // ใช้ url ตรง เพราะ route name 'user' ซ้ำ (index + user/{id}) → route() ต้องการ id
    ],

    'Department' => [
        'title' => 'แผนก',
        'icon' => 'ti-shopping-cart',
        'route_name' => 'department.index',
        'permission' => 'department read',
    ],

    'Employee' => [
        'title' => 'พนักงาน',
        'icon' => 'ti-users',
        'route_name' => 'employee.index',
        'permission' => 'employee read',
    ],

    'Role' => [
        'title' => 'จัดการสิทธิ์',
        'icon' => 'ti-shield-lock',
        'route_name' => 'role.index',
        'permission' => 'role read',
    ],


];
