<?php


namespace app\common\validate\api;

use think\Validate;

class UserDynamic extends Validate
{

    protected $rule = [
        'id|动态id' => ['require'],
        'content|动态内容' => ['require'],
        'state|点赞状态'  => ['require'],
        'dynamicId|动态id' => ['require'],
        'commentsText|评论内容'=>['require']
    ];

    protected $scene = [
        'addDynamic' => ['content'],
        'setDynamicLike' => ['id','state'],
        'getDynamicById'=> ['dynamicId'],
        'sendComments'=>['dynamicId','commentsText'],
        'delDynamic' => ['id' ,'uid']

    ];

}