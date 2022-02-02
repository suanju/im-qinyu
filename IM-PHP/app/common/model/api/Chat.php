<?php


namespace app\common\model\api;


use think\Model;
use function Swoole\Coroutine\Http\get;

class Chat extends Model
{

    protected $table = "im_api_chat";

    // 定义时间戳字段名
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    public function getRecord($uid, $fid, $page, $rows)
    {
        return self::with('getPortrait')
            ->whereIn('uid', [$uid, $fid])
            ->whereIn('fid', [$fid, $uid])
            ->order('created_at', 'desc')
            ->field(['id', 'uid', 'message','messageType', 'created_at'])
            ->page($page, $rows)
            ->select();
    }

    public function getPortrait(){
        return $this->belongsTo(User::class,'uid')->bind(['portrait']);
    }

}