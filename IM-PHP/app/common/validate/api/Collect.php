<?php


namespace app\common\validate\api;

use think\Validate;

class Collect extends Validate
{

    protected $rule = [
        'id|收藏id' => ['require'],
        'messageId|消息id' => ['require'],
        'uid|用户id' => ['require'],
    ];

    protected $scene = [
        'setCollect' => ['messageId','uid'],
        'delCollect' =>['id','uid']

    ];

}