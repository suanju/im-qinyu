<?php

namespace app\api\controller;

use app\BaseController;
use think\App;
use app\common\business\api\UserInfo as Business;
use app\common\validate\api\UserInfo as Validate;

class UserInfo extends BaseController
{
    private $business = null;

    public function __construct(App $app)
    {
        $this->business = new Business();
        parent::__construct($app);
    }
   //获取自己资料信息
    public function getOneselfInfo()
    {
        $id = $this->getUid();
        $data = $this->business->getOneselfInfo($id);
        return $this->success($data);
    }
    //修改自己资料信息
    public function setOneselfInfo(){
        $data['uid'] = $this->getUid();
        $data['signature'] = $this->request->param('signature');
        $data['hometown'] = $this->request->param('hometown');
        $data['birthday'] = $this->request->param('birthday');
        try {
            validate(Validate::class)->scene("setOneselfInfo")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $msg = $this->business->setOneselfInfo($data);
        return  $this->success($msg);
    }


    //根据id 获取对方信息
    public function getInfoById(){
        $data['id'] = $this->request->param('id');
        $data['uid'] = $this->request->param('uid');
        try {
            validate(Validate::class)->scene("getInfoById")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }

        $info = $this->business->getInfoById($data);
        return $this->success($info);
    }

}