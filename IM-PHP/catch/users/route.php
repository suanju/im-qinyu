<?php
// +----------------------------------------------------------------------
// | CatchAdmin [Just Like ～ ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017~{$year} http://catchadmin.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( https://github.com/yanwenwu/catch-admin/blob/master/LICENSE.txt )
// +----------------------------------------------------------------------
// | Author: JaguarJack [ njphper@gmail.com ]
// +----------------------------------------------------------------------
// you should use `$router`
/* @var think\Route $router */
$router->group(function () use($router) {
    // user 路由
    $router->resource('api_users', catchAdmin\users\controller\User::class);
    // chat 路由
    $router->resource('api_chat', catchAdmin\users\controller\Chat::class);

     // 切换状态
    $router->put('api_users/switch/status/<id>', '\catchAdmin\users\controller\User@switchStatus');
    $router->put('api_users/profile', '\catchAdmin\users\controller\User@profile');
    $router->get('api_users/info', '\catchAdmin\users\controller\User@info');
    $router->get('api_users/export', '\catchAdmin\users\controller\User@export');
})->middleware('auth');