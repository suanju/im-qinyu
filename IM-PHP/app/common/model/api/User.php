<?php

namespace app\common\model\api;

use think\Model;

class User extends Model
{
    protected $table = "im_api_user";

    // 定义时间戳字段名
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    public function updateLoginInfo($data)
    {
        $result = $this->findByUserNameWithStatus($data['username']);
        return $result->allowField(['last_login_token'])->save($data);
    }

    public function findByUserNameWithStatus($username)
    {
        return $this->where('username', $username)->where('status', 1)->find();
    }

    public function findByIdWithStatus($id)
    {
        return $this->where('id', $id)->where('status', 1)->find();
    }

    public function findByUserName($username)
    {
        return $this->where('username', $username)->find();
    }

    //获取用户头像

    public function getPortrait($uid)
    {
        $data = $this->where('id', $uid)->where('status', 1)->field('portrait,username')->find();
        return $data['portrait'];
    }

    //获取用户名
    public function getUsername($uid)
    {
        $data = $this->where('id', $uid)->where('status', 1)->field('username')->find();
        return $data['username'];
    }

    //获取用户信息(用户名）
    public function getUserInfoByName($name,$data)
    {
        $data = $this->where('username',$name)->field($data)->find();
        return $data;
    }
    //获取用户信息（id）
    public function getUserInfoByID($id,$data)
    {
        $data = $this->where('id',$id)->field($data)->find();
        return $data;
    }



}