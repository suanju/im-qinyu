<?php

namespace app\common\model\api;

use think\Model;

class UserDynamic extends Model
{
    protected $table = "im_api_user_dynamic";

    protected $json = ['img','likes','comments'];

    protected $jsonAssoc = true;

    // 定义时间戳字段名
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';



}