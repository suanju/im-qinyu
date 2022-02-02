<?php


namespace app\common\model\api;


use think\Model;

class ChatGroup extends Model
{

    protected $table = "im_api_chat_group";

    // 定义时间戳字段名
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    public function getRecord($fid, $page, $rows)
    {
        return self::with('getPortrait')
            ->where('fid', $fid)
            ->order('create_time', 'desc')
            ->field(['id', 'uid', 'message','messageType', 'create_time'])
            ->page($page, $rows)
            ->select();
    }

    public function getPortrait(){
        return $this->belongsTo(User::class,'uid')->bind(['portrait']);
    }

}