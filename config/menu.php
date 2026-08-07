<?php

return [
    'ColorMatching' => [
        'title' => 'T-เทียบสี',
        'icon' => 'ti-palette',
        'route_name' => 'color_matching.index',
        'permission' => 'color_matching read',
    ],
    'Quotation' => [
        'title' => 'Q-ใบเสนอราคา',
        'icon' => 'ti-file-invoice',
        'route_name' => 'quotation.index',
        'permission' => 'quotation read',
    ],
    'Saleinfo' => [
        'title' => 'I-กำหนดราคา',
        'icon' => 'ti-tag',
        'route_name' => 'saleinfo.index',
        'permission' => 'saleinfo read',
    ],
    'Order' => [
        'title' => 'O-Order',
        'icon' => 'ti-shopping-cart',
        'route_name' => 'order.index',
        'permission' => 'order read',
    ],

    'Production' => [
        'title' => 'P-แผนการผลิต',
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
            // กลุ่มย่อย "รายงาน" (ซ้อนอีกชั้นใต้แผนการผลิต) — เพิ่ม 2026-07-31
            'ProductionReport' => [
                'title' => 'รายงาน',
                'icon' => '',
                'menu_parent' => 'Production',
                'permission' => 'productionplanning read',
                'sub_menu' => [
                    'ProductionReportMachine' => [
                        'title' => 'รายงานผลิตตามเครื่องจักร',
                        'icon' => '',
                        'menu_parent' => 'ProductionReport',
                        'route_name' => 'production.report.machine.index',
                        'permission' => 'productionplanning read',
                    ],
                    'ProductionReportEmployee' => [
                        'title' => 'รายงานผลิตตามพนักงาน',
                        'icon' => '',
                        'menu_parent' => 'ProductionReport',
                        'route_name' => 'production.report.employee.index',
                        'permission' => 'productionplanning read',
                    ],
                    'OrderChangeRequest' => [
                        'title' => 'ใบขอเปลี่ยนแปลงคำสั่งซื้อภายใน',
                        'icon' => '',
                        'menu_parent' => 'ProductionReport',
                        'route_name' => 'production.orderchange.index',
                        'permission' => 'productionplanning read',
                    ],
                ],
            ],
        ],
    ],
    // ข้อมูลสินค้า (tb_products) — ต่อจากแผนการผลิต — เพิ่ม 07/08/2569
    'Product' => [
        'title' => 'ข้อมูลสินค้า',
        'icon' => 'ti-box',
        'route_name' => 'product.index',
        'permission' => 'product read',
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
    // 'Permission' => [
    //     'title' => 'สิทธิ์การใช้งาน',
    //     'icon' => 'ti-shield-lock',
    //     'route_name' => 'permission.index',
    //     'permission' => 'permission read',
    // ],
    // 'LinkUser' => [
    //     'title' => 'User',
    //     'icon' => 'ti-id-badge',
    //     'url' => 'user', // ใช้ url ตรง เพราะ route name 'user' ซ้ำ (index + user/{id}) → route() ต้องการ id
    // ],

    'Department' => [
        'title' => 'แผนก',
        'icon' => 'ti-shopping-cart',
        'route_name' => 'department.index',
        'permission' => 'department read',
    ],

    'Machine' => [
        'title' => 'เครื่องจักร',
        'icon' => 'ti-tools',
        'route_name' => 'machine.index',
        'permission' => 'machine read',
    ],

    'ProdMethod' => [
        'title' => 'วิธีการผลิต',
        'icon' => 'ti-list-check',
        'route_name' => 'prodmethod.index',
        'permission' => 'prodmethod read',
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

    'Temp' => [
        'title' => 'Temp',
        'icon' => 'ti-list',
        'route_name' => 'temp.index',
        'permission' => 'temp read',
    ],


];
