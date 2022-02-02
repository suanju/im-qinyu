<?php

namespace app\api\controller;

use app\BaseController;
use app\common\business\api\User as Business;
use app\common\validate\api\User as Validate;
use think\exception\ValidateException;
use think\App;


class User extends BaseController
{
    private $business = null;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->business = new Business();

    }

    public function register()
    {
        $data['username'] = $this->request->param("username");
        $data['password'] = $this->request->param("password");
        $data['email'] = $this->request->param("email");
        $data['verCode'] = $this->request->param("verCode");
        try {
            validate(Validate::class)->scene("login_register")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $this->business->register($data);
        return $this->success("注册成功！");
    }

    public function login()
    {
        $data['username'] = $this->request->param("username", '', 'htmlspecialchars');
        $data['password'] = $this->request->param("password", '', 'htmlspecialchars');
        try {
            validate(Validate::class)->scene("login_register")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $errCode = $this->business->login($data);
        return $this->success($errCode);

    }

    //验证码
    public function verificationCode()
    {
        $data['email'] = $this->request->param("email", '', 'htmlspecialchars');
        try {
            validate(Validate::class)->scene("email")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $status = $this->business->verificationCode($data['email']);
        return $this->success($status);
    }

    //重置密码验证码
    public function verificationCodeReset()
    {
        $data['username'] = $this->request->param('username');
        try {
            validate(Validate::class)->scene('isUserName')->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $status = $this->business->verificationCodeReset($data['username']);
        return $this->success($status);

    }

    //重置密码
    public function passwordReset()
    {
        $data['username'] = $this->request->param('username');
        $data['password'] = $this->request->param('password');
        $data['verCode'] = $this->request->param('verCode');
        try {
            validate(Validate::class)->scene('passwordReset')->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $status = $this->business->passwordReset($data);
        return $this->success($status);
    }

    //token验证
    public function isLogin()
    {
        return $this->success("token验证成功！");
    }

    public function logoff()
    {
        $this->business->logoff($this->getToken());
        return $this->success("退出登陆成功！");
    }

    //添加好友
    public function addFriend()
    {
        $data['username'] = $this->request->param("username");
        $data['message'] = $this->request->param("message");
        $data['remark'] = $this->request->param("remark");
        $data['user'] = $this->getUser();
        $data['token'] = $this->getToken();
        try {
            validate(Validate::class)->scene("addFriend")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $this->business->addFriend($data);

        return $this->success("好友申请已发送！");
    }

    //删除好友
    public function delFriend(){
        $data['id'] = $this->request->param('id');
        $data['uid'] = $this->getUid();

        try {
            validate(Validate::class)->scene("delFriend")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $state =  $this->business->delFriend($data);

        return $this->success($state);

    }

    //好友列表
    public function friendList()
    {
        $list = $this->business->friendList($this->getUid());
        return $this->success($list);
    }

    //好友列表
    public function friendArr()
    {
        $list = $this->business->friendArr($this->getUid());
        return $this->success($list);
    }

    //最近聊天
    public function latelyChat()
    {
        $list = $this->business->latelyChat($this->getUid());
        if (!empty($list)) {
            return $this->success($list);
        }
        return $this->error('暂时没有最近聊天大好友哦！');
    }

    //删除最近聊天(好友）
    public function delLatelyChat()
    {
        $data['uid'] = $this->getUid();
        $data['id'] = $this->request->param('id');
        $state = $this->business->delLatelyChat($data);
        return $this->success($state);

    }
    //删除最近聊天(群聊）
    public function delLatelyChatGroup()
    {
        $data['uid'] = $this->getUid();
        $data['id'] = $this->request->param('id');
        $state = $this->business->delLatelyChatGroup($data);
        return $this->success($state);

    }


    //好友处理
    public function handleFriend()
    {
        $data['decision'] = $this->request->param("decision");
        $data['target'] = $this->request->param("target");
        $data['uid'] = $this->getUid();
        $data['uidToken'] = $this->getToken();
        try {
            validate(Validate::class)->scene("handleFriend")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $this->business->handleFriend($data);
        return $this->success("处理完成！");
    }

    //群聊处理
    public function handleGroup(){
        $data['decision'] = $this->request->param("decision");
        $data['target'] = $this->request->param("target");
        $data['uid'] = $this->getUid();
        $data['uidToken'] = $this->getToken();
        try {
            validate(Validate::class)->scene("handleGroup")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $this->business->handleGroup($data);
        return $this->success("处理完成！");
    }

    //获取对方用户头像
    public function getfidportrait()
    {
        $data['id'] = $this->request->param('id');
        try {
            validate(Validate::class)->scene("get_portrait")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
        $portrsit = $this->business->getPortrait($data);
        return $this->success($portrsit);

    }

    //获取自己用户头像
    public function getPortrait()
    {
        $data['id'] = $this->getUid();
        try {
            validate(Validate::class)->scene("get_portrait")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }

        $portrsit = $this->business->getPortrait($data);
        return $this->success($portrsit);
    }

    //获取自己用户信息
    public function getUserInfoByID()
    {
        $data['id'] = $this->getUid();
        try {
            validate(Validate::class)->scene("get_portrait")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }

        $info = $this->business->getUserInfoByID($data);
        return $this->success($info);

    }

    //搜索好友
    public function searchFriend()
    {
        $data['value'] = $this->request->param('value');
        try {
            validate(Validate::class)->scene("searchFriend")->check($data);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }

        $info = $this->business->searchFriend($data);
        return $this->success($info);

    }
    //已读添加好友信息
    public function readNewFriend(){
        $data['uid'] = $this->getUid();
        $info = $this->business->readNewFriend($data);
        return $this->success($info);
    }

    //已读添加群聊信息
    public function readNewGroup(){
        $data['uid'] = $this->getUid();
        $info = $this->business->readNewGroup($data);
        return $this->success($info);
    }

    //更新头像

    public function updatePortrait(){
        $file = $this->request->file('file');
        $uid = $this->getUid();

        $info = $this->business->updatePortrait($uid,$file);

        return $this->success($info);

    }


}
