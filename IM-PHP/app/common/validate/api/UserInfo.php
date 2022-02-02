<?php

namespace app\common\validate\api;

use think\Validate;

class UserInfo extends Validate
{
    protected $rule = [
        'uid|ID' => ['require'],
        'id' => ['require'],
        'signature|个性签名' => [ 'max' => 80],
        'hometown|家乡' => ['max' => 20],
        'birthday|生日' => ['max' => 20],
    ];

    protected $scene = [
        'setOneselfInfo'=> ['uid','signature','hometown','birthday'],
        'getInfoById' =>['id','uid'],
    ];
}