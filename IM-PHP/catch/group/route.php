<?php
// +----------------------------------------------------------------------
// | CatchAdmin [Just Like ～ ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017~2022 http://catchadmin.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( https://github.com/yanwenwu/catch-admin/blob/master/LICENSE.txt )
// +----------------------------------------------------------------------
// | Author: JaguarJack [ njphper@gmail.com ]
// +----------------------------------------------------------------------
/* @var \think\Route $router */
$router->group(function () use($router) {
     $router->resource('group', catchAdmin\group\controller\Group::class);
     //群成员
    $router->get('group/getGroupMembers/<name>', '\catchAdmin\group\controller\Group@getGroupMembers');
})->middleware('auth');