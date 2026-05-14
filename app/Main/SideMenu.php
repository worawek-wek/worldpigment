<?php

namespace App\Main;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SideMenu
{
    /**
     * List of side menu items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function menu()
    {
        return [
            'icon' => 'home',
            'route_name' => 'dashboard',
            'params' => [
            ],
            'title' => 'Dashboard'
        ];
        $user = Auth::user();
        $ref_user = [];
        if($user){
            $ref_user = User::where('ref_user_id', $user->id)->first();
        }
        $Dashboard = [
            'icon' => 'home',
            'route_name' => 'dashboard',
            'params' => [
            ],
            'title' => 'Dashboard'
        ];
        $users = [
            'icon' => 'users',
            'route_name' => 'user',
            'params' => [
            ],
            'title' => 'พนักงาน'
        ];
        $user_time = [
            'icon' => 'clock',
            'route_name' => 'user-time',
            'params' => [
            ],
            'title' => 'สรุปเวลาพนักงาน'
        ];
        $positions = [
            'icon' => 'award',
            'route_name' => 'position',
            'params' => [
            ],
            'title' => 'ตำแหน่ง'
        ];
        $Leave = [
            'icon' => 'umbrella',
            'route_name' => 'leave',
            'params' => [
            ],
            'title' => 'ลา'
        ];
        $check_employee_leave = [
            'icon' => 'user-check',
            'route_name' => 'check-employee-leave',
            'params' => [
            ],
            'title' => 'การอนุมัติการลา'
        ];
        $work_shift = [
            'icon' => 'file-text',
            'route_name' => 'work-shift',
            'params' => [
            ],
            'title' => 'กะการทำงาน'
        ];
        $welfare = [
            'icon' => 'gift',
            'route_name' => 'welfare',
            'params' => [
            ],
            'title' => 'สวัสดิการ'
        ];
        $annual_holiday = [
            'icon' => 'calendar',
            'route_name' => 'annual-holiday',
            'params' => [
            ],
            'title' => 'ตารางวันหยุด'
        ];
        $export_excel = [
            'icon' => 'file-text',
            'route_name' => 'export-excel',
            'params' => [
            ],
            'title' => 'Export Excel'
        ];
        
        $admin = [
            $Dashboard,
            $users,
            $user_time,
            $positions,
            $Leave,
            $check_employee_leave,
            $work_shift,
            $welfare,
            $annual_holiday,
            $export_excel,
        ];
        
        $employee = [
            $Dashboard,
            $Leave,
            $user_time,
            $annual_holiday,
            $welfare
        ];

        $boss = [
            $Dashboard,
            $Leave,
            $check_employee_leave,
            $user_time,
            $annual_holiday,
            $welfare
        ];
        
        if($user){
            if(in_array($user->ref_position_id,[1,3])){
                return $admin;
            }
        }
        if($ref_user && !in_array($user->ref_position_id,[1,3])){
                return $boss;
        }
        return $employee;
        
        $ME2 = [
            // 'dashboard2' => [
            //     'icon' => 'home',
            //     'title' => 'Dashboard2',
            //     'sub_menu' => [
            //         'dashboard2-overview-1' => [
            //             'icon' => '',
            //             'route_name' => 'dashboard2-overview-1',
            //             'params' => [
            //                 'layout' => 'side-menu',
            //             ],
            //             'title' => 'Overview 1'
            //         ],
            //         'dashboard2-overview-2' => [
            //             'icon' => '',
            //             'route_name' => 'dashboard2-overview-2',
            //             'params' => [
            //                 'layout' => 'side-menu',
            //             ],
            //             'title' => 'Overview 2'
            //         ],
            //         'dashboard2-overview-3' => [
            //             'icon' => '',
            //             'route_name' => 'dashboard2-overview-3',
            //             'params' => [
            //                 'layout' => 'side-menu',
            //             ],
            //             'title' => 'Overview 3'
            //         ],
            //         'dashboard2-overview-4' => [
            //             'icon' => '',
            //             'route_name' => 'dashboard2-overview-4',
            //             'params' => [
            //                 'layout' => 'side-menu',
            //             ],
            //             'title' => 'Overview 4'
            //         ]
            //     ]
            // ],
            'Dashboard1' => [
                'icon' => 'home',
                'route_name' => 'dashboard',
                'params' => [
                ],
                'title' => 'Dashboard1'
            ],
            'users' => [
                'icon' => 'users',
                'title' => 'ผู้ใช้งาน',
                'sub_menu' => [
                    // 'users-layout-1' => [
                    //     'icon' => '',
                    //     'route_name' => 'users-layout-1',
                    //     'params' => [
                    //         'layout' => 'side-menu'
                    //     ],
                    //     'title' => 'Layout 1'
                    // ],
                    // 'users-layout-2' => [
                    //     'icon' => '',
                    //     'route_name' => 'users-layout-2',
                    //     'params' => [
                    //         'layout' => 'side-menu'
                    //     ],
                    //     'title' => 'Layout 2'
                    // ],
                    'users' => [
                        'icon' => '',
                        'route_name' => 'user',
                        'params' => [
                        ],
                        'title' => 'พนักงาน'
                    ],
                    'user-time' => [
                        'icon' => '',
                        'route_name' => 'user-time',
                        'params' => [
                        ],
                        'title' => 'สรุปเวลาพนักงาน'
                    ],
                    'positions' => [
                        'icon' => '',
                        'route_name' => 'position',
                        'params' => [
                        ],
                        'title' => 'ตำแหน่ง'
                    ],
                    'Leave' => [
                        'icon' => '',
                        'route_name' => 'leave',
                        'params' => [
                        ],
                        'title' => 'ลา'
                    ],
                    'check-employee-leave' => [
                        'icon' => '',
                        'route_name' => 'check-employee-leave',
                        'params' => [
                        ],
                        'title' => 'การอนุมัติการลา'
                    ]
                ]
            ],
            // 'user-setting' => [
            //     'icon' => 'file-text',
            //     'route_name' => 'user-setting',
            //     'params' => [
            //     ],
            //     'title' => 'การตั้งค่าผู้ใช้งาน'
            // ],
            'work-shift' => [
                'icon' => 'file-text',
                'route_name' => 'work-shift',
                'params' => [
                ],
                'title' => 'กะการทำงาน'
            ],
            'welfare' => [
                'icon' => 'gift',
                'route_name' => 'welfare',
                'params' => [
                ],
                'title' => 'สวัสดิการ'
            ],
            'annual-holiday' => [
                'icon' => 'calendar',
                'route_name' => 'annual-holiday',
                'params' => [
                ],
                'title' => 'ตารางวันหยุด'
            ],
            'export-excel' => [
                'icon' => 'file-text',
                'route_name' => 'export-excel',
                'params' => [
                ],
                'title' => 'Export Excel'
            ],
            'menu-layout' => [
                'icon' => 'box',
                'title' => 'Menu Layout',
                'sub_menu' => [
                    'side-menu' => [
                        'icon' => '',
                        'route_name' => 'dashboard2-overview-1',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Side Menu'
                    ],
                    'simple-menu' => [
                        'icon' => '',
                        'route_name' => 'dashboard2-overview-1',
                        'params' => [
                            'layout' => 'simple-menu'
                        ],
                        'title' => 'Simple Menu'
                    ],
                    'top-menu' => [
                        'icon' => '',
                        'route_name' => 'dashboard2-overview-1',
                        'params' => [
                            'layout' => 'top-menu'
                        ],
                        'title' => 'Top Menu'
                    ]
                ]
            ],
            'Dashboard' => [
                'icon' => 'home',
                'route_name' => 'inbox',
                'params' => [
                    'layout' => 'side-menu'
                ],
                'title' => 'Inbox'
            ],
            'inbox' => [
                'icon' => 'inbox',
                'route_name' => 'inbox',
                'params' => [
                    'layout' => 'side-menu'
                ],
                'title' => 'Inbox'
            ],
            'file-manager' => [
                'icon' => 'hard-drive',
                'route_name' => 'file-manager',
                'params' => [
                    'layout' => 'side-menu'
                ],
                'title' => 'File Manager'
            ],
            'point-of-sale' => [
                'icon' => 'credit-card',
                'route_name' => 'point-of-sale',
                'params' => [
                    'layout' => 'side-menu'
                ],
                'title' => 'Point of Sale'
            ],
            'chat' => [
                'icon' => 'message-square',
                'route_name' => 'chat',
                'params' => [
                    'layout' => 'side-menu'
                ],
                'title' => 'Chat'
            ],
            'post' => [
                'icon' => 'file-text',
                'route_name' => 'post',
                'params' => [
                    'layout' => 'side-menu'
                ],
                'title' => 'Post'
            ],
            'calendar' => [
                'icon' => 'calendar',
                'route_name' => 'calendar',
                'params' => [
                    'layout' => 'side-menu'
                ],
                'title' => 'Calendar'
            ],
            'devider',
            'crud' => [
                'icon' => 'edit',
                'title' => 'Crud',
                'sub_menu' => [
                    'crud-data-list' => [
                        'icon' => '',
                        'route_name' => 'crud-data-list',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Data List'
                    ],
                    'crud-form' => [
                        'icon' => '',
                        'route_name' => 'crud-form',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Form'
                    ]
                ]
            ],
            'profile' => [
                'icon' => 'trello',
                'title' => 'Profile',
                'sub_menu' => [
                    'profile-overview-1' => [
                        'icon' => '',
                        'route_name' => 'profile-overview-1',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Overview 1'
                    ],
                    'profile-overview-2' => [
                        'icon' => '',
                        'route_name' => 'profile-overview-2',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Overview 2'
                    ],
                    'profile-overview-3' => [
                        'icon' => '',
                        'route_name' => 'profile-overview-3',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Overview 3'
                    ]
                ]
            ],
            'pages' => [
                'icon' => 'layout',
                'title' => 'Pages',
                'sub_menu' => [
                    'wizards' => [
                        'icon' => '',
                        'title' => 'Wizards',
                        'sub_menu' => [
                            'wizard-layout-1' => [
                                'icon' => '',
                                'route_name' => 'wizard-layout-1',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Layout 1'
                            ],
                            'wizard-layout-2' => [
                                'icon' => '',
                                'route_name' => 'wizard-layout-2',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Layout 2'
                            ],
                            'wizard-layout-3' => [
                                'icon' => '',
                                'route_name' => 'wizard-layout-3',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Layout 3'
                            ]
                        ]
                    ],
                    'blog' => [
                        'icon' => '',
                        'title' => 'Blog',
                        'sub_menu' => [
                            'blog-layout-1' => [
                                'icon' => '',
                                'route_name' => 'blog-layout-1',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Layout 1'
                            ],
                            'blog-layout-2' => [
                                'icon' => '',
                                'route_name' => 'blog-layout-2',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Layout 2'
                            ],
                            'blog-layout-3' => [
                                'icon' => '',
                                'route_name' => 'blog-layout-3',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Layout 3'
                            ]
                        ]
                    ],
                    'pricing' => [
                        'icon' => '',
                        'title' => 'Pricing',
                        'sub_menu' => [
                            'pricing-layout-1' => [
                                'icon' => '',
                                'route_name' => 'pricing-layout-1',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Layout 1'
                            ],
                            'pricing-layout-2' => [
                                'icon' => '',
                                'route_name' => 'pricing-layout-2',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Layout 2'
                            ]
                        ]
                    ],
                    'invoice' => [
                        'icon' => '',
                        'title' => 'Invoice',
                        'sub_menu' => [
                            'invoice-layout-1' => [
                                'icon' => '',
                                'route_name' => 'invoice-layout-1',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Layout 1'
                            ],
                            'invoice-layout-2' => [
                                'icon' => '',
                                'route_name' => 'invoice-layout-2',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Layout 2'
                            ]
                        ]
                    ],
                    'faq' => [
                        'icon' => '',
                        'title' => 'FAQ',
                        'sub_menu' => [
                            'faq-layout-1' => [
                                'icon' => '',
                                'route_name' => 'faq-layout-1',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Layout 1'
                            ],
                            'faq-layout-2' => [
                                'icon' => '',
                                'route_name' => 'faq-layout-2',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Layout 2'
                            ],
                            'faq-layout-3' => [
                                'icon' => '',
                                'route_name' => 'faq-layout-3',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Layout 3'
                            ]
                        ]
                    ],
                    'login' => [
                        'icon' => '',
                        'route_name' => 'login',
                        'params' => [
                            'layout' => 'login'
                        ],
                        'title' => 'Login'
                    ],
                    'register' => [
                        'icon' => '',
                        'route_name' => 'register',
                        'params' => [
                            'layout' => 'login'
                        ],
                        'title' => 'Register'
                    ],
                    'error-page' => [
                        'icon' => '',
                        'route_name' => 'error-page',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Error Page'
                    ],
                    'update-profile' => [
                        'icon' => '',
                        'route_name' => 'update-profile',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Update profile'
                    ],
                    'change-password' => [
                        'icon' => '',
                        'route_name' => 'change-password',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Change Password'
                    ]
                ]
            ],
            'devider',
            'components' => [
                'icon' => 'inbox',
                'title' => 'Components',
                'sub_menu' => [
                    'grid' => [
                        'icon' => '',
                        'title' => 'Grid',
                        'sub_menu' => [
                            'regular-table' => [
                                'icon' => '',
                                'route_name' => 'regular-table',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Regular Table'
                            ],
                            'tabulator' => [
                                'icon' => '',
                                'route_name' => 'tabulator',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Tabulator'
                            ]
                        ]
                    ],
                    'overlay' => [
                        'icon' => '',
                        'title' => 'Overlay',
                        'sub_menu' => [
                            'modal' => [
                                'icon' => '',
                                'route_name' => 'modal',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Modal'
                            ],
                            'slide-over' => [
                                'icon' => '',
                                'route_name' => 'slide-over',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Slide Over'
                            ],
                            'notification' => [
                                'icon' => '',
                                'route_name' => 'notification',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Notification'
                            ],
                        ]
                    ],
                    'tab' => [
                        'icon' => '',
                        'route_name' => 'tab',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Tab'
                    ],
                    'accordion' => [
                        'icon' => '',
                        'route_name' => 'accordion',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Accordion'
                    ],
                    'button' => [
                        'icon' => '',
                        'route_name' => 'button',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Button'
                    ],
                    'alert' => [
                        'icon' => '',
                        'route_name' => 'alert',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Alert'
                    ],
                    'progress-bar' => [
                        'icon' => '',
                        'route_name' => 'progress-bar',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Progress Bar'
                    ],
                    'tooltip' => [
                        'icon' => '',
                        'route_name' => 'tooltip',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Tooltip'
                    ],
                    'dropdown' => [
                        'icon' => '',
                        'route_name' => 'dropdown',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Dropdown'
                    ],
                    'typography' => [
                        'icon' => '',
                        'route_name' => 'typography',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Typography'
                    ],
                    'icon' => [
                        'icon' => '',
                        'route_name' => 'icon',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Icon'
                    ],
                    'loading-icon' => [
                        'icon' => '',
                        'route_name' => 'loading-icon',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Loading Icon'
                    ]
                ]
            ],
            'forms' => [
                'icon' => 'sidebar',
                'title' => 'Forms',
                'sub_menu' => [
                    'regular-form' => [
                        'icon' => '',
                        'route_name' => 'regular-form',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Regular Form'
                    ],
                    'datepicker' => [
                        'icon' => '',
                        'route_name' => 'datepicker',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Datepicker'
                    ],
                    'tom-select' => [
                        'icon' => '',
                        'route_name' => 'tom-select',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Tom Select'
                    ],
                    'file-upload' => [
                        'icon' => '',
                        'route_name' => 'file-upload',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'File Upload'
                    ],
                    'wysiwyg-editor' => [
                        'icon' => '',
                        'title' => 'Wysiwyg Editor',
                        'sub_menu' => [
                            'wysiwyg-editor-classic' => [
                                'icon' => '',
                                'route_name' => 'wysiwyg-editor-classic',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Classic'
                            ],
                            'wysiwyg-editor-inline' => [
                                'icon' => '',
                                'route_name' => 'wysiwyg-editor-inline',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Inline'
                            ],
                            'wysiwyg-editor-balloon' => [
                                'icon' => '',
                                'route_name' => 'wysiwyg-editor-balloon',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Balloon'
                            ],
                            'wysiwyg-editor-balloon-block' => [
                                'icon' => '',
                                'route_name' => 'wysiwyg-editor-balloon-block',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Balloon Block'
                            ],
                            'wysiwyg-editor-document' => [
                                'icon' => '',
                                'route_name' => 'wysiwyg-editor-document',
                                'params' => [
                                    'layout' => 'side-menu'
                                ],
                                'title' => 'Document'
                            ],
                        ]
                    ],
                    'validation' => [
                        'icon' => '',
                        'route_name' => 'validation',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Validation'
                    ]
                ]
            ],
            'widgets' => [
                'icon' => 'hard-drive',
                'title' => 'Widgets',
                'sub_menu' => [
                    'chart' => [
                        'icon' => '',
                        'route_name' => 'chart',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Chart'
                    ],
                    'slider' => [
                        'icon' => '',
                        'route_name' => 'slider',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Slider'
                    ],
                    'image-zoom' => [
                        'icon' => '',
                        'route_name' => 'image-zoom',
                        'params' => [
                            'layout' => 'side-menu'
                        ],
                        'title' => 'Image Zoom'
                    ]
                ]
            ]
            /////////////////////////////
        ];
        return $ME2;
    }
}
