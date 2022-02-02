<?php

namespace app\api\controller;

use app\BaseController;
use think\App;
use app\common\business\api\UserDynamic as Business;
use app\common\validate\api\UserDynamic as Validate;

class UserDynamic extends BaseController
{
    private $business = NULL;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->business = new Business();

    }
    //上传图片
    public function uploadPic(){
        $file = $this->request->file();
        $saveName = $this->business->uploadPic($file["file"]);
        return $this->success($saveName);
    }

    //添加动态
    public function addDynamic(){
        $data['uid'] = $this->getUid();
        $data['content'] = $this->request->param('content');
        $data['img'] = $this->request->param('img');
        try {
            validate(Validate::class)->scene("addDynamic")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $state = $this->business->addDynamic($data);
        return $this->success($state);

    }
    //获取动态
    public function getDynamic(){
        $state = $this->business->getDynamic();
        return $this->success($state);
    }

    //获取自己动态
    public function getMyDynamic(){
        $id = $this->getUid();
        $state = $this->business->getMyDynamic($id);
        return $this->success($state);
    }
    //点赞

    public function setDynamicLike(){
        $data['id'] =  $this->request->param('id');
        $data['uid'] = $this->getUid();
        $data['state'] =  $this->request->param('state');
        try {
            validate(Validate::class)->scene("setDynamicLike")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }

        $state = $this->business->setDynamicLike($data);
        return $this->success($state);

    }

    //获取动态by id
    public function getDynamicById(){
        $data['dynamicId'] = $this->request->param('dynamicId');
        try {
            validate(Validate::class)->scene("getDynamicById")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $state = $this->business->getDynamicById($data);
        return $this->success($state);
    }
    //发布评论
    public function sendComments(){
        $data['dynamicId'] = $this->request->param('dynamicId');
        $data['uid'] = $this->getUid();
        $data['commentsText'] =  $this->request->param('commentsText');
        try {
            validate(Validate::class)->scene("sendComments")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $state = $this->business->sendComments($data);
        return $this->success($state);
    }

    //删除动态
    public function delDynamic(){
        $data['id'] = $this->request->param('id');
        $data['uid'] = $this->getUid();
        try {
            validate(Validate::class)->scene("delDynamic")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $state = $this->business->delDynamic($data);
        return $this->success($state);

    }


}