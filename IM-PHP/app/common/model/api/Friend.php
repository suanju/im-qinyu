<?php

namespace app\common\model\api;


use think\Model;

class Friend extends Model
{

    protected $table = "im_api_friend";


    // 定义时间戳字段名
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';


    public function friendList($uid){
        return self::with('userinfo')
            -> where('uid', $uid)
            -> where('status', 1)
            -> field(['fid'])
            -> select();
    }

    public function isFriend($uid, $fid){
        $u = $this -> where('uid', $uid) -> where('fid', $fid) -> where('status', 1) -> find();
        $f = $this -> where('uid', $fid) -> where('fid', $uid) -> where('status', 1) -> find();
        return !empty($u) && !empty($f);
    }

    public function userinfo(){
        return $this -> belongsTo(User::class, 'fid') -> bind(['username','portrait']);
    }

}