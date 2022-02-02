<?php

namespace app\common\business\api;

use app\common\model\api\UserDynamic as Model;
use app\common\model\api\User as userModel;
use think\Exception;

class UserDynamic
{
    protected $model = null;
    protected $userModel = null;

    public function __construct()
    {
        $this->model = new Model();
        $this->userModel = new userModel();
    }
    //上传头像
    public function uploadPic ($file){
        $saveName = \think\facade\Filesystem::disk('dynamicPic')->putFile( 'topic', $file,'md5');
        return $saveName;
    }
    //添加动态
    public  function addDynamic($data){
       for ($i = 0 ;$i < count($data['img']); $i++){
           $data['img'][$i] = '/static/appStatic/dynamicPic/'. $data['img'][$i];
       }
        $state = $this->model->save($data);
        return $state;
    }

    public  function getDynamic(){
        $state = $this->model->order('created_at', 'asc')->select();
        foreach ($state as $k =>$v){
            $state[$k]['name'] = $this->userModel->getUserInfoByID($state[$k]['uid'],['username'])['username'];
            $state[$k]['avatar'] = $this->userModel->getUserInfoByID($state[$k]['uid'],['portrait'])['portrait'];
        }
        return $state;
    }

    //获取自己动态
    public  function getMyDynamic($id){
        $state = $this->model->where('uid',$id)->order('created_at', 'asc')->select();
        foreach ($state as $k =>$v){
            $state[$k]['name'] = $this->userModel->getUserInfoByID($state[$k]['uid'],['username'])['username'];
            $state[$k]['avatar'] = $this->userModel->getUserInfoByID($state[$k]['uid'],['portrait'])['portrait'];
        }
        return $state;
    }

    //动态点赞
    public function setDynamicLike($data){
        $info = $this->model->find($data['id']);
        if ($data['state']){
            //点赞操作
                $arr =  $info->likes;
                $arr[$data['uid']] = time();
                $info->likes = $arr;
        }else{
            //取消点赞
              $arr  =  $info->likes;
              unset($arr[$data['uid']]);
              if (empty($arr)){
                  $arr = NULL;
              }
              $info->likes = $arr;
        }

        return  $info->save();

    }

    public function getDynamicById($data)
    {
        $state = $this->model->find($data['dynamicId'])->toArray();;
        $state['name'] = $this->userModel->getUserInfoByID($state['uid'], ['username'])['username'];
        $state['avatar'] = $this->userModel->getUserInfoByID($state['uid'], ['portrait'])['portrait'];
        if (! empty($state['comments'])) {
            foreach ($state['comments'] as $k => $v) {
                $state['comments'][$k]['name'] = $this->userModel->getUserInfoByID($v['uid'], ['username'])['username'];
                $state['comments'][$k]['avatar'] = $this->userModel->getUserInfoByID($v['uid'], ['portrait'])['portrait'];
            }
        }
        return $state;
    }

    //发布评论
    public function sendComments($data){
        $info = $this->model->find($data['dynamicId']);
        $arr =  $info->comments;
        $arr[] = ['uid' => $data['uid'], 'text'=>$data['commentsText'] ,'time' => time()];
        $info->comments = $arr;
        return  $info->save();
    }

    //删除评论
    public function delDynamic($data){
        $info = $this->model->find($data['id']);
        if (!$info)   throw new Exception("动态不存在");
        if ($info->uid == $data['uid']){
           return $info->delete();
        }else{
            throw new Exception("不能删除他人动态");
        }
    }
}