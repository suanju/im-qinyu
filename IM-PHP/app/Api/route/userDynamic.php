<?php

use think\facade\Route;
use app\api\middleware\IsLogin;

Route::group('UserDynamic', function (){
    Route::rule('uploadPic', '/api/UserDynamic/uploadPic');
}) ;