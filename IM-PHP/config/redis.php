<?php
/**
redis //键值对
 */

return [

    //激活Token
    "active_pre" => "active_account_pre_",
    //登陆Token
    "token_pre" => "access_token_pre_",
    //登陆Token持续时间(一天)
    "token_expire" => 24 * 3600,
    //登录验证码
    "code_pre" => "login_pre_",
    //登录验证码过期时间
    "code_expire" => 120,
    //文件数据过期时间 15min
    'file_expire' => 3600 / 4,
    //ws
    'socket_pre' => "socket_uid_",
    //注册验证码集合
    'ver_code' => 'ver_code',
    //注册验证码--有效时间/秒
    'ver_code_time' => 'ver_code_time'

];