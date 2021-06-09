<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
\think\Route::rule('app/:key/[:lang]', 'down/index/app');
\think\Route::rule('down/:key', 'down/index/down'); //updateLinks
\think\Route::rule('updateLinks/:ids', 'down/index/updateLinks');
\think\Route::rule('testflight/ids/:ids', 'down/index/testflight');

//APP加载TF链接
\think\Route::rule('loading/ids/:ids', 'down/index/loading');

//别名做链接-实现不掉链
\think\Route::rule('alias/:alias/[:lang]', 'down/index/alias');

//testflight/ids/

return [
    //别名配置,别名只能是映射到控制器且访问时必须加上请求的方法
    '__alias__'   => [],
    //变量规则
    '__pattern__' => [],
    //        域名绑定到模块
    //        '__domain__'  => [
    //            'admin' => 'admin',
    //            'api'   => 'api',
    //        ],
];
