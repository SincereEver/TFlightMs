<?php

return [
    [
        'name' => 'key',
        'title' => 'BT密钥',
        'type' => 'string',
        'value' => 'NQ8aBTXNcD6lr7Z9nKK4Uc0mPdMwMbTS',
        'rule' => 'required',
        'tip' => '请先在BT面板中开启API功能',
        'extend' => '',
    ],
    [
        'name' => 'url',
        'title' => '访问地址',
        'type' => 'string',
        'value' => 'http://39.109.123.232:62288',
        'rule' => 'required',
        'tip' => '请填写BT面板地址,格式为"http://XXX.XXX.XXX.XXX:62288"',
        'extend' => '',
    ],
    [
        'type' => 'bool',
        'name' => 'admin',
        'title' => '仅限admin用户访问',
        'value' => '1',
        'content' => [
            1 => '开启',
            0 => '关闭',
        ],
        'tip' => '',
        'rule' => '',
        'extend' => '',
    ],
    [
        'type' => 'bool',
        'name' => 'linux',
        'title' => '强制linux系统',
        'value' => '1',
        'content' => [
            1 => '开启',
            0 => '关闭',
        ],
        'tip' => '',
        'rule' => '',
        'extend' => '',
    ],
    [
        'type' => 'number',
        'name' => 'cycle',
        'title' => '首页刷新周期',
        'value' => '5000',
        'content' => '',
        'tip' => '毫秒',
        'rule' => '',
        'extend' => '',
    ],
];
