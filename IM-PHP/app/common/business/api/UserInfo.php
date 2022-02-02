<?php

namespace app\common\business\api;

use app\common\model\api\UserInfo as UserInfoModel;

class UserInfo
{
    private $userInfoMobel = NULL;

    public function __construct(){
        $this->userInfoMobel =new UserInfoModel();
    }

    public function getOneselfInfo($id){
        $data = $this->userInfoMobel->where('uid',$id)->find();
        return $data;

    }
    public function setOneselfInfo($data){
        $userInfo = $this->userInfoMobel->where('uid',$data['uid'])->find();
        if (empty($userInfo)){
            $msg = $this->userInfoMobel->insert($data);
        }else{
          foreach ($data as $k => $v ){
              $userInfo->$k = $v;
          }
          $msg = $userInfo->save();
        }
        return $msg;
    }

    //根据id 获取用户信息

    public function getInfoById($data){
        $info = $this->userInfoMobel->getInfoById($data['id'],$data['uid']);
        return $info;

    }




}