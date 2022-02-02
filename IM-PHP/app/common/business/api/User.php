<?php

namespace app\common\business\api;

use app\common\business\lib\Str;
use app\common\model\api\Friend;
use app\common\model\api\User as UserModel;
use app\common\model\api\Group as groupModel;
use app\common\model\api\MyGroup as myGroupModel;
use Exception;
use think\cache\driver\Redis;
use think\facade\Db;
use WebSocket\Client;
use app\common\model\api\UserInfo as UserInfoModel;

class User
{
    private $userModel = NULL;
    private $friendModel = NULL;
    private $str = NULL;
    private $redis = NULL;
    private $groupModel = NULL;
    private $myGroupModel = NULL;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->friendModel = new Friend();
        $this->str = new Str();
        $this->redis = new  Redis();
        $this->groupModel = new groupModel();
        $this-> myGroupModel = new myGroupModel();
    }

    //好友处理
    public function friendList($uid)
    {
        $user = $this->friendModel->friendList($uid);
        $user = $this->str->chartSort($user);
        return $user;
    }

    public function friendArr($uid){
        $user = $this->friendModel->friendList($uid);
        return $user;
    }

    //最近聊天
    public function latelyChat($uid)
    {
        $data = $this->redis->get(config('redis.socket_pre') . $uid);
        $list = [];
        if (!empty($data['latelyChat']) || !empty($data['latelyChatGroup'])) {
            if (!empty($data['latelyChat'])) {
                foreach ($data['latelyChat'] as $k => $v) {
                    $userList = [
                        'id' => $k,
                        'message' => $v['message'],
                        'time' => $v['time'],
                        'type' => 'friend',
                        'username' => $this->userModel->getUsername($k),
                        'portrait' => $this->userModel->getPortrait($k),
                        //计算未读消息
                        'unread' => !empty($data['delay_list'][$k]) == true ? $data['delay_list'][$k]['count'] : 0
                    ];
                    array_push($list, $userList);
                }
            }
            if ( !empty($data['latelyChatGroup'])) {
                foreach ($data['latelyChatGroup'] as $k => $v) {
                    $groupList = [
                        'id' => $k,
                        'message' => $v['message'],
                        'time' => $v['time'],
                        'type' => 'group',
                        'username' => $this->groupModel->getGroupName($k),
                        'portrait' => $this->groupModel->getPortrait($k),
                        //计算未读消息
                        'unread' => !empty($data['delayGroup_list'][$k]) == true ? $data['delayGroup_list'][$k]['count'] : 0
                    ];
                    array_push($list, $groupList);
                }
            }

            return $list;
        }
        return [];

    }

    //删除聊天(好友）
    public function delLatelyChat($data){
        //清空消息
        $this->clearMessage($data['uid'],$data['id'],'latelyChat');
        $list = $this->redis->get(config('redis.socket_pre') . $data['uid']);
        unset($list['latelyChat'][$data['id']]);
        $this->redis->set(config('redis.socket_pre') . $data['uid'],$list);
        return true;
    }
    //删除聊天(群聊）
    public function delLatelyChatGroup($data){
        $this->clearMessage($data['uid'],$data['id'],'latelyChatGroup');
        $list = $this->redis->get(config('redis.socket_pre') . $data['uid']);
        unset($list['latelyChatGroup'][$data['id']]);
        $this->redis->set(config('redis.socket_pre') . $data['uid'],$list);
        return true;
    }

    //清空未读消息
    public function clearMessage($uid,$fid,$type){
        if ($type == 'latelyChat'){
            $data = $this->redis->get(config('redis.socket_pre') . $uid);
            unset($data['delay_list'][$fid]);
            $this -> redis -> set(config('redis.socket_pre') . $uid, $data);
        }else{
            $data = $this->redis->get(config('redis.socket_pre') . $uid);
            unset($data['latelyChatGroup'][$fid]);
            $this -> redis -> set(config('redis.socket_pre') . $uid, $data);
        }

    }

    public function handleFriend($data)
    {
        $socket = $this->redis->get(config('redis.socket_pre') . $data['uid']);
        //定义验证消息
        $validationData = $socket['apply_list'][$data['target']]['message'];
        //好友申请发送者token
        $sendToken = $this->userModel->getUserInfoByID($data['target'],['last_login_token']);
        if (empty($socket['apply_list']) || !array_key_exists($data['target'], $socket['apply_list'])) {
            throw new Exception("该好友申请不存在！");
        }
        Db::startTrans();
        try {
            $this->redis->multi();
            if ((boolean)$data['decision']) {
                $lists = [
                    [
                        'uid' => $data['target'],
                        'fid' => $data['uid'],
                        'remark' => $socket['apply_list'][$data['target']]['remark']
                    ], [
                        'uid' => $data['uid'],
                        'fid' => $data['target']
                    ]
                ];
                $this->friendModel->saveAll($lists);
            }
            unset($socket['apply_list'][$data['target']]);
            $this->redis->set(config('redis.socket_pre') . $data['uid'], $socket);
            $this->redis->exec();
            Db::commit();
        } catch (Exception $exception) {
            $this->redis->discard();
            Db::rollback();
            throw new Exception("未知异常！");
        }
        //添加好友后执行发送一条消息(给添加者)
        $send = [
            'type' => 'chat',
            'message' => $validationData,
            'uid' => $data['uid'],
            'messageType' => 'newFriend'
        ];
        $sendClient = new Client(config('config.ws_host') . '?type=chat_uid_' . $data['uid'] . '&token=' . $sendToken['last_login_token']);
        $sendClient->send(json_encode($send));
        $sendClient->close();
        //添加好友后执行发送一条消息(给发送者)
        $accept = [
            'type' => 'chat',
            'message' => '我通过了你的好友请求！',
            'uid' => $data['target'],
            'messageType' => 'newFriend'
        ];
        $acceptClient = new Client(config('config.ws_host') .'?type=chat_uid_' . $data['target'] . '&token=' . $data['uidToken']);
        $acceptClient->send(json_encode($accept));
        $acceptClient->close();
    }

    public function handleGroup($data){
        $socket = $this->redis->get(config('redis.socket_pre') . $data['uid']);
        //群聊信息
        $groupInfo = $this->groupModel->find($data['target']);
        if (empty($socket['applyGroup_list']) || !array_key_exists($data['target'], $socket['applyGroup_list'])) {
            throw new Exception("该群聊邀请不存在！");
        }
        Db::startTrans();
        try {
            $this->redis->multi();
            if ((boolean)$data['decision']) {
               $addGroup = [
                   'uid'=>$data['uid'],
                   'group_id'=>$data['target']
               ];
               $this->myGroupModel->save($addGroup);
               //群聊当中添加
                $idsAdd = $groupInfo->ids;
                $idsAdd[$data['uid']] = ['type' =>'member', 'time' => time()];
                $groupInfo->ids = $idsAdd;
                $groupInfo->save();
            }
            unset($socket['applyGroup_list'][$data['target']]);
            $this->redis->set(config('redis.socket_pre') . $data['uid'], $socket);
            $this->redis->exec();
            Db::commit();
        } catch (Exception $exception) {
            $this->redis->discard();
            Db::rollback();
            throw new Exception($exception);
        }
    }

    public function readNewFriend($data)
    {
        $socket = $this->redis->get(config('redis.socket_pre') . $data['uid']);
        foreach ($socket['apply_list'] as $key => $value) {
            if ( $socket['apply_list'][$key]['type'] == 'addFriend'){
                $socket['apply_list'][$key]['state'] = 1;
            }
        }
        $this->redis->set(config('redis.socket_pre') . $data['uid'], $socket);
        return '清除成功！';

    }

    public function readNewGroup($data){
        $socket = $this->redis->get(config('redis.socket_pre') . $data['uid']);
        foreach ($socket['apply_list'] as $key => $value) {
            if ( $socket['apply_list'][$key]['type'] == 'addGroup'){
                $socket['apply_list'][$key]['state'] = 1;
            }
        }
        $this->redis->set(config('redis.socket_pre') . $data['uid'], $socket);
        return '清除成功！';
    }
    //注册
    public function register($data)
    {
        //判断验证码
        $verData = $this->redis->get(config('redis.ver_code'), []);
        if (empty($verData[$data['email']])) throw new Exception("请先发送验证码！");
        if ($verData[$data['email']] != $data['verCode']) {
            throw new Exception("验证码错误！");
        }
        $isExist = $this->userModel->findByUserName($data['username']);
        $isGroupExist = $this->groupModel->findByGroupName($data['username']);
        if (!empty($isExist) || !empty($isGroupExist)) {
            throw new Exception("名字被用烂了");
        }
        $data['password_salt'] = $this->str->salt(5);
        $data['password'] = md5($data['password_salt'] . $data['password'] . $data['password_salt']);
        //随机头像
        $data['portrait'] = '/static/appStatic/userPortrait/system/auto' . rand(1, 10) . '.png';
        $this->userModel->save($data);
        //创建资料卡
        $UserInfoModel =  new UserInfoModel();
        $UserInfoModel->save(['uid'=> $this->userModel->id]);
    }


    public function login($data)
    {
        $isExist = $this->userModel->findByUserNameWithStatus($data['username']);
        if (empty($isExist)) {
            throw new Exception("用户名不存在！");
        }
        $password = md5($isExist['password_salt'] . $data['password'] . $isExist['password_salt']);
        if ($password != $isExist['password']) {
            throw new Exception("密码填写错误！");
        }
        $this->redis->delete(config('redis.token_pre') . $isExist['last_login_token']);
        $token = $this->str->createToken($isExist['username']);
        $userinfo = [
            'token' => $token,
            'userinfo' => $this->userModel->getUserInfoByName($data['username'], ['id', 'username', 'portrait'])
        ];
        $this->userModel->updateLoginInfo([
            'username' => $isExist['username'],
            'last_login_token' => $token
        ]);
        $this->redis->set(config('redis.token_pre') . $token, [
            'id' => $isExist['id'],
            'username' => $isExist['username']
        ]);
        return $userinfo;
    }

    //退出登入
    public function logoff($token)
    {
        $this->redis->delete(config('redis.token_pre') . $token);
    }

    //添加好友
    public function addFriend($data)
    {
        $isExist = $this->userModel->findByUserNameWithStatus($data['username']);
        if (empty($isExist)) {
            throw new Exception("用户名不存在！");
        }

        $socket = $this->redis->get(config('redis.socket_pre') . $isExist['id']);
        if (!empty($socket['apply_list'])) {
            foreach ($socket['apply_list'] as $key => $value) {
                if ($key == $data['user']['id']) {
                    throw new Exception("请勿重复申请！");
                }
            }
        }
        if ($this->friendModel->isFriend($data['user']['id'], $isExist['id'])) {
            throw new Exception("已成为好友！");
        }

        if ($isExist['id'] == $data['user']['id']) {
            throw new Exception("不能加自己为好友！");
        }

        $send = [
            'type' => 'addFriend',
            'uid' => $data['user']['id'],
            'username' => $data['user']['username'],
            'target' => $isExist['id'],
            'message' => $data['message'],
            'remark' => $data['remark'],
        ];
        $client = new Client(config('config.ws_host') . '?type=public&token=' . $data['token']);
        $client->send(json_encode($send));
        $client->close();


    }

    //删除好友
    public function delFriend($data){
        $isExist = $this->userModel->findByIdWithStatus($data['uid']);
        if (! $this->friendModel->isFriend($data['id'], $isExist['id'])) {
            throw new Exception("他不是您的好友！");
        }
        //删除数据库中
        $delU =   $this->friendModel->where('uid',$data['uid'])->where('fid',$data['id'])->delete();
        $delF =  $this->friendModel->where('uid',$data['id'])->where('fid',$data['uid'])->delete();
        //删除最近聊天
        $list = $this->redis->get(config('redis.socket_pre') . $data['uid']);
        unset($list['latelyChat'][$data['id']]);
        $this->redis->set(config('redis.socket_pre') . $data['uid'],$list);

        return $delU && $delF ;

    }

    //获取头像
    public function getPortrait($data)
    {
        $portrait = $this->userModel->find($data['id']);
        return $portrait['portrait'];
    }

    //获取用户信息
    public function getUserInfoByID($data)
    {
        $info = $this->userModel->getUserInfoByID($data['id'], ['id', 'username', 'portrait', 'email']);
        return $info;
    }

    //验证码
    public function verificationCode($email)
    {
        $code = randCode();
        $title = '注册验证码';
        $verData = $this->redis->get(config('redis.ver_code'));
        $verData[$email] = $code;
        $this->redis->set(config('redis.ver_code'), $verData, config('redis.ver_code_time'));
        $text = $code . '【5分钟有效】';
        $status = sendMail($email, $title, $text);
        return $status;
    }

    //重置验证码
    public function verificationCodeReset($name)
    {
        $userDate = $this->userModel->findByUserName($name);
        $code = randCode();
        $title = '重置验证码';
        $verData = $this->redis->get(config('redis.ver_code'));
        $verData[$userDate['email']] = $code;
        $this->redis->set(config('redis.ver_code'), $verData, config('redis.ver_code_time'));
        $text = $code . '【5分钟有效】';
        $status = sendMail($userDate['email'], $title, $text);
        return $status;
    }

    //重置密码
    public function passwordReset($data)
    {
        //判断验证码
        $userInfo = $this->userModel->findByUserName($data['username']);
        $verData = $this->redis->get(config('redis.ver_code'), []);
        if (empty($verData[$userInfo['email']])) throw new Exception("请先发送验证码！");
        if ($verData[$userInfo['email']] != $data['verCode']) {
            throw new Exception("验证码错误！");
        }
        $data['password_salt'] = $this->str->salt(5);
        //不带入验证码更新
        unset($data['verCode']);
        $data['password'] = md5($data['password_salt'] . $data['password'] . $data['password_salt']);
        $this->userModel->where('username', $data['username'])->save($data);

    }

    //搜索好友
    public function searchFriend($data)
    {
        //要获取的字段信息
        $field = ['id', 'username', 'email', 'portrait'];
        $userList = $this->userModel->where('username', $data['value'])->field($field)->find();
        if (empty($userList)) {
            $userList = $this->userModel->where('username', 'like', '%' . $data['value'] . '%')->field($field)->find();
        }
        return $userList;

    }

    //更新头像
    public function updatePortrait($uid,$file){
        $saveName = \think\facade\Filesystem::disk('userPortrait')->putFile( 'user', $file,'md5');
        $saveName = '/static/appStatic/userPortrait/'.$saveName;
        $userInfo = $this->userModel->find($uid);
        $userInfo->portrait = $saveName;
        $userInfo->save();
        return $saveName;


    }



}