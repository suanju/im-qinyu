<?php

namespace app\api\controller;

use app\BaseController;
use app\common\business\api\Group as Business;
use app\common\validate\api\Group as Validate;
use think\App;

class Group extends BaseController
{

    public $business = null;
    public $validate = null;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->business = new Business();
        $this->validate = new Validate();
    }

    public function crateGroup(){
        $data['uid']= $this->getUid();
        $data['ids'] = $this->request->param('ids');
        $data['name'] = $this->request->param('name');
        try {
            validate(Validate::class)->scene("crateGroup")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $state = $this->business->crateGroup($data);

        return $this->success($state);

    }
    public function getGroup(){
        $data['uid'] = $this->getUid();
        $list = $this->business->getGroup($data);
        return $this->success($list);
    }

    //获取群聊信息
    public function getGroupInfo(){
        $data['uid'] = $this->getUid();
        $data['id'] = $this->request->param('id');
        $list = $this->business->getGroupInfo($data);
        return $this->success($list);
    }
    //设置群宣言
    public function setManifesto(){
        $data['uid'] = $this->getUid();
        $data['id'] = $this->request->param('id');
        $data['manifesto'] = $this->request->param('manifesto');
        try {
            validate(Validate::class)->scene("setManifesto")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $state = $this->business->setManifesto($data);

        return $this->success($state);
    }

    //解散群聊
    public function delGroup(){
        $data['uid'] = $this->getUid();
        $data['id'] = $this->request->param('id');
        try {
            validate(Validate::class)->scene("delGroup")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $state = $this->business->delGroup($data);

        return $this->success($state);
    }
    //退出群聊
    public function exitGroup(){
        $data['uid'] = $this->getUid();
        $data['id'] = $this->request->param('id');
        try {
            validate(Validate::class)->scene("exitGroup")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $state = $this->business->exitGroup($data);

        return $this->success($state);
    }

    //踢出群聊（单个）
    public function delMember(){
        $data['uid'] = $this->getUid();
        $data['id'] = $this->request->param('id');
        $data['ids'] = $this->request->param('ids');
        try {
            validate(Validate::class)->scene("delMember")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $state = $this->business->delMember($data);

        return $this->success($state);
    }

    //邀请好友加入
    public function invitationGroup(){
        $data['uid'] = $this->getUid();
        $data['id'] = $this->request->param('id');
        $data['ids'] = $this->request->param('ids');
        $data['token'] = $this->getToken();
        try {
            validate(Validate::class)->scene("invitationGroup")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $state = $this->business->invitationGroup($data);

        return $this->success($state);
    }
    //搜索群聊
    public function searchGroup()
    {
        $data['value'] = $this->request->param('value');
        try {
            validate(Validate::class)->scene("searchFriend")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }

        $info = $this->business->searchGroup($data);
        return $this->success($info);

    }

    //更新头像

    public function updatePortrait(){
        $file = $this->request->file('file');
        $uid = $this->getUid();
        $id = $this -> request -> header('groupId');
        if (!empty($file) && !empty($uid) && !empty($id)){
            return $this->error('缺少参数！');
        }
        $info = $this->business->updatePortrait($uid,$id,$file);
        return $this->success($info);

    }

    //添加好友
    public function addGroup()
    {
        $data['name'] = $this->request->param("name");
        $data['message'] = $this->request->param("message");
        $data['id'] = $this->getUid();
        try {
            validate(Validate::class)->scene("addGroup")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $this->business->addGroup($data);

        return $this->success("群聊申请已发送！");
    }

}