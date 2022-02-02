<?php

use think\facade\Route;
use app\Api\middleware\IsLogin;

Route::group('View/User', function (){
    Route::rule('register', '/api/View/register', 'GET');
    Route::rule('login', '/api/View/login', 'GET');
    Route::rule('index', '/api/View/index', 'GET');
});

Route::group('User', function (){
    Route::rule('register', '/api/User/register', 'POST');
    Route::rule('login', '/api/User/login', 'POST');
});

Route::group('User', function (){
    Route::rule('getPortrait', '/api/User/getPortrait', 'POST');
    Route::rule('getUserInfoByID', '/api/User/getUserInfoByID', 'POST');
    Route::rule('latelyChat', '/api/User/latelyChat', 'POST');
    Route::rule('friendList', '/api/User/friendList', 'POST');
    Route::rule('handleFriend', '/api/User/handleFriend', 'POST');
    Route::rule('addFriend', '/api/User/addFriend', 'POST');
    Route::rule('logoff', '/api/User/logoff', 'POST');
    Route::rule('isLogin', '/api/User/isLogin', 'POST');
}) -> middleware(IsLogin::class);