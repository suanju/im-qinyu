<?php


namespace app\common\validate\api;

use think\Validate;

class User extends Validate
{

    protected $rule = [
        'username|用户名' => ['require', 'max' => 20, 'min' => 2],
        'password|密码' => ['require', 'max' => 20, 'min' => 6],
        'message|留言' => ['max' => 20],
        'decision' => ['require', 'between:0,1'],
        'target' => ['require', 'number'],
        'id' => ['require','min'=>0],
        'uid' => ['require','min'=>0],
        'verCode|验证码' => ['require','min'=>0],
        'email' => ['email'],
        'value' =>['require'],
        'remark|好友备注' =>['require']
    ];

    protected $scene = [
        'handleFriend' => ['decision', 'target'],
        'handleGroup' => ['decision', 'target'],
        'addFriend' => ['username', 'message','remark'],
        'login_register' => ['username', 'password','email'],
        'get_portrait' => ['id'],
        'isUserName' => ['username'],
        'email' => ['email'],
        'passwordReset' =>['username','password','verCode'],
        'searchFriend' =>['value'],
        'delFriend' => ['id','uid']
    ];

}