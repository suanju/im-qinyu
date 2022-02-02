<?php

namespace app\common\model\api;

use think\Model;


class Group extends Model
{
    protected $table = "im_api_group";

    protected $json = ['ids'];

    protected $jsonAssoc = true;


    // 定义时间戳字段名
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    //获取群头像

    public function getPortrait($uid)
    {
        $data = $this->where('id', $uid)->field('portrait,name')->find();
        return $data['portrait'];
    }

    //获取群名
    public function getGroupName($uid)
    {
        $data = $this->where('id', $uid)->field('name')->find();
        return $data['name'];
    }


    public function findByGroupName($name)
    {
        return $this->where('name', $name)->find();
    }

    //获取群聊信息
    public function getGroupInfo($id){
        $data =  $this->where('id', $id)->find();

        return $data;


    }

}