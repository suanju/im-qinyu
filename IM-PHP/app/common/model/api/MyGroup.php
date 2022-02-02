<?php

namespace app\common\model\api;

use think\Model;

class MyGroup extends Model
{

    // 定义时间戳字段名
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    protected $table = "im_api_my_group";

    public function getGroup($uid){
       return  self::with('getGroupInfo')->where('uid',$uid)->select();
    }
    public  function getGroupInfo(){
      return  $this->belongsTo(Group::class,'group_id')->bind(['ids','name','manifesto','portrait']);

    }


}