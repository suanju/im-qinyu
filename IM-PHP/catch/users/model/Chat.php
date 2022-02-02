<?php

namespace catchAdmin\users\model;

use catchAdmin\users\model\search\ChatSearch;
use catcher\base\CatchModel;
use catcher\traits\db\BaseOptionsTrait;
use catcher\traits\db\ScopeTrait;

/**
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $email
 * @property string $password_salt
 * @property string $last_login_token
 * @property string $portrait
 * @property int $status
 * @property int $create_time
 * @property int $update_time
 */
class Chat extends CatchModel
{
    use BaseOptionsTrait, ScopeTrait;
    use ChatSearch;


    public $field = [
        //自增id
        'id',
        // 用户uid
        'uid',
        // 好友uid
        'fid',
        //聊天
        'message',
        // 消息类型
        'messageType',
        // 创建时间
        'created_at',
        //更新时间
        'updated_at',
        //删除
        'deleted_at'
    ];

    public $name = 'api_chat';


    public function getList()
    {

        // 分页列表
        return self::with('getInfo')->catchSearch()
            ->catchOrder()
            ->paginate();
    }

    //绑定用户信息
    public function getInfo(){
        return $this->belongsTo(User::class,'fid','id')->bind(['username']);
    }
}