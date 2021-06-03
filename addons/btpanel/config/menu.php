<?php
/**
 * 菜单配置文件
 */

return [
    [
        "type"      => "file",
        "name"      => "btpanel",
        "title"     => "宝塔管理",
        "icon"      => "fa fa-list",
        "condition" => "",
        "remark"    => "",
        "ismenu"    => 1,
        "sublist"   => [
            [
                "type"      => "file",
                "name"      => "btpanel/index",
                "title"     => "控制台",
                "icon"      => "fa fa-circle-o",
                "condition" => "",
                "remark"    => "",
                "ismenu"    => 1,
                "sublist"   => [
                    [
                        "type"      => "file",
                        "name"      => "btpanel/index/index",
                        "title"     => "首页",
                        "icon"      => "fa fa-circle-o",
                        "condition" => "",
                        "remark"    => "",
                        "ismenu"    => 0
                    ],
                    [
                        "type"      => "file",
                        "name"      => "btpanel/index/add",
                        "title"     => "添加",
                        "icon"      => "fa fa-circle-o",
                        "condition" => "",
                        "remark"    => "",
                        "ismenu"    => 0
                    ],
                    [
                        "type"      => "file",
                        "name"      => "btpanel/index/edit",
                        "title"     => "编辑",
                        "icon"      => "fa fa-circle-o",
                        "condition" => "",
                        "remark"    => "",
                        "ismenu"    => 0
                    ],
                    [
                        "type"      => "file",
                        "name"      => "btpanel/index/del",
                        "title"     => "删除",
                        "icon"      => "fa fa-circle-o",
                        "condition" => "",
                        "remark"    => "",
                        "ismenu"    => 0
                    ],
                    [
                        "type"      => "file",
                        "name"      => "btpanel/index/multi",
                        "title"     => "批量更新",
                        "icon"      => "fa fa-circle-o",
                        "condition" => "",
                        "remark"    => "",
                        "ismenu"    => 0
                    ]
                ]
            ],
            [
                "type"      => "file",
                "name"      => "btpanel/crontab",
                "title"     => "定时任务",
                "icon"      => "fa fa-circle-o",
                "condition" => "",
                "remark"    => "",
                "ismenu"    => 1,
                "sublist"   => [
                    [
                        "type"      => "file",
                        "name"      => "btpanel/crontab/index",
                        "title"     => "首页",
                        "icon"      => "fa fa-circle-o",
                        "condition" => "",
                        "remark"    => "",
                        "ismenu"    => 0
                    ],
                    [
                        "type"      => "file",
                        "name"      => "btpanel/crontab/add",
                        "title"     => "添加",
                        "icon"      => "fa fa-circle-o",
                        "condition" => "",
                        "remark"    => "",
                        "ismenu"    => 0
                    ],
                    [
                        "type"      => "file",
                        "name"      => "btpanel/crontab/edit",
                        "title"     => "编辑",
                        "icon"      => "fa fa-circle-o",
                        "condition" => "",
                        "remark"    => "",
                        "ismenu"    => 0
                    ],
                    [
                        "type"      => "file",
                        "name"      => "btpanel/crontab/del",
                        "title"     => "删除",
                        "icon"      => "fa fa-circle-o",
                        "condition" => "",
                        "remark"    => "",
                        "ismenu"    => 0
                    ],
                    [
                        "type"      => "file",
                        "name"      => "btpanel/crontab/multi",
                        "title"     => "批量更新",
                        "icon"      => "fa fa-circle-o",
                        "condition" => "",
                        "remark"    => "",
                        "ismenu"    => 0
                    ]
                ]
            ],
            [
                "type"      => "file",
                "name"      => "btpanel/logs",
                "title"     => "运行日志",
                "icon"      => "fa fa-circle-o",
                "condition" => "",
                "remark"    => "",
                "ismenu"    => 1,
                "sublist"   => [
                    [
                        "type"      => "file",
                        "name"      => "btpanel/logs/index",
                        "title"     => "首页",
                        "icon"      => "fa fa-circle-o",
                        "condition" => "",
                        "remark"    => "",
                        "ismenu"    => 0
                    ],
                    [
                        "type"      => "file",
                        "name"      => "btpanel/logs/add",
                        "title"     => "添加",
                        "icon"      => "fa fa-circle-o",
                        "condition" => "",
                        "remark"    => "",
                        "ismenu"    => 0
                    ],
                    [
                        "type"      => "file",
                        "name"      => "btpanel/logs/edit",
                        "title"     => "编辑",
                        "icon"      => "fa fa-circle-o",
                        "condition" => "",
                        "remark"    => "",
                        "ismenu"    => 0
                    ],
                    [
                        "type"      => "file",
                        "name"      => "btpanel/logs/del",
                        "title"     => "删除",
                        "icon"      => "fa fa-circle-o",
                        "condition" => "",
                        "remark"    => "",
                        "ismenu"    => 0
                    ],
                    [
                        "type"      => "file",
                        "name"      => "btpanel/logs/multi",
                        "title"     => "批量更新",
                        "icon"      => "fa fa-circle-o",
                        "condition" => "",
                        "remark"    => "",
                        "ismenu"    => 0
                    ]
                ]
            ],
            [
                "type"      => "file",
                "name"      => "btpanel/monitor",
                "title"     => "服务器监控",
                "icon"      => "fa fa-circle-o",
                "condition" => "",
                "remark"    => "",
                "ismenu"    => 1,
                "sublist"   => [
                    [
                        "type"      => "file",
                        "name"      => "btpanel/monitor/index",
                        "title"     => "首页",
                        "icon"      => "fa fa-circle-o",
                        "condition" => "",
                        "remark"    => "",
                        "ismenu"    => 0
                    ],
                    [
                        "type"      => "file",
                        "name"      => "btpanel/monitor/add",
                        "title"     => "添加",
                        "icon"      => "fa fa-circle-o",
                        "condition" => "",
                        "remark"    => "",
                        "ismenu"    => 0
                    ],
                    [
                        "type"      => "file",
                        "name"      => "btpanel/monitor/edit",
                        "title"     => "编辑",
                        "icon"      => "fa fa-circle-o",
                        "condition" => "",
                        "remark"    => "",
                        "ismenu"    => 0
                    ],
                    [
                        "type"      => "file",
                        "name"      => "btpanel/monitor/del",
                        "title"     => "删除",
                        "icon"      => "fa fa-circle-o",
                        "condition" => "",
                        "remark"    => "",
                        "ismenu"    => 0
                    ],
                    [
                        "type"      => "file",
                        "name"      => "btpanel/monitor/multi",
                        "title"     => "批量更新",
                        "icon"      => "fa fa-circle-o",
                        "condition" => "",
                        "remark"    => "",
                        "ismenu"    => 0
                    ]
                ]
            ]
        ]
    ]
];
