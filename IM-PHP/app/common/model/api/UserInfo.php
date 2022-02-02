<?php

namespace app\common\model\api;

use think\Model;
use app\common\model\api\Friend as friendModel;

class UserInfo extends Model
{
    protected $table = "im_api_user_info";

    // 定义时间戳字段名
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    //或许用户信息（是否为好友）
    public function getInfoById($id ,$uid){
       $info = self::with('getUserInfo')
           ->where('uid',$id)
           ->field(['uid','signature','hometown','birthday'])
           ->find();
       $isFriend = $this->isFriend($id,$uid);
       $info->isFriend = $isFriend;

       return $info;
    }

    //关联主表信息
    public function getUserInfo(){
        return $this->belongsTo(User::class,'uid')->bind(['username','portrait','email']);
    }

    //判断是否为好友
    public function isFriend($uid, $fid){
        $friendModel = new friendModel;
        $u = $friendModel -> where('uid', $uid) -> where('fid', $fid) -> where('status', 1) -> find();
        $f = $friendModel -> where('uid', $fid) -> where('fid', $uid) -> where('status', 1) -> find();
        return !empty($u) && !empty($f);
    }


}