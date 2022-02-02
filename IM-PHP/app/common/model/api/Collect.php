<?php

namespace app\common\model\api;

use think\Model;

class Collect extends Model
{
    protected $table = "im_api_collect";


    // 定义时间戳字段名
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    public function getCollect($uid)
    {
        return self::with('getUserInfo')
            ->where('uid', $uid)
            ->select();
    }

    public function getUserInfo(){
        return $this->belongsTo(User::class,'send_id')->bind(['username','portrait']);
    }

}