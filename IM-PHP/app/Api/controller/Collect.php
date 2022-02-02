<?php

namespace app\api\controller;

use app\BaseController;
use app\common\business\api\Collect as Business;
use app\common\validate\api\Collect as Validate;
use think\App;

class Collect extends BaseController
{
    public $business = null;
    public $validate = null;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->business = new Business();
        $this->validate = new Validate();
    }
    //收藏消息（好友）
    public function setCollect(){
        $data['messageId'] = $this->request->param('messageId');
        $data['uid'] = $this->getUid();
        try {
            validate(Validate::class)->scene("setCollect")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $state = $this->business->setCollect($data);

        return $this->success($state);
    }
    //收藏消息（群聊)
    public function setCollectGroup(){
        $data['messageId'] = $this->request->param('messageId');
        $data['uid'] = $this->getUid();
        try {
            validate(Validate::class)->scene("setCollect")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $state = $this->business->setCollectGroup($data);

        return $this->success($state);
    }
    //获取消息
    public function getCollect(){
        $data['uid'] = $this->getUid();
        $info = $this->business->getCollect($data);
        return $this->success($info);
    }

    //删除收藏
    public function delCollect(){
        $data['id'] = $this->request->param('id');
        $data['uid'] = $this->getUid();
        try {
            validate(Validate::class)->scene("delCollect")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $info = $this->business->delCollect($data);
        return $this->success($info);

    }


}