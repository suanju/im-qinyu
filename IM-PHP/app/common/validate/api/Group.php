<?php

namespace app\common\validate\api;

use think\Validate;

class Group extends Validate
{

    protected $rule = [
        'ids|群成员' => ['require'],
        'uid|用户id' => ['require'],
        'id|群id' => ['require'],
        'ids|群成员id' => ['require'],
        'value}群聊名称' => ['require'],
        'name|群聊名称' => ['require'],
        'message|验证消息' => ['require'],
    ];

    protected $scene = [
        'crateGroup' => ['ids', 'uid'],
        'setManifesto' => ['id'],
        'delGroup' => ['id'],
        'exitGroup' => ['id'],
        'delMember' => ['id', 'ids'],
        'invitationGroup' => ['id', 'ids'],
        'searchFriend' => ['value'],
        'addGroup' => ['name', 'message']

    ];

}