<?php


namespace server\base;


use think\cache\driver\Redis;
use app\common\model\api\User;
use app\common\model\api\Group as groupModel;


class Base
{

    protected $redis = NULL;
    protected $user = NULL;
    protected $groupModel = NUll;

    public function __construct() {
        $this -> redis = new Redis();
        $this -> user = new User();
        $this -> groupModel = new groupModel();
    }

    public function  handle($token, $type, $ws, $fd){
        $user = $this -> getUser($token);
        if (empty($user)){
            $ws -> close($fd);
        }else{
            $ws -> bind($fd, $user['id']);
            if (strpos($type, 'chat_uid_') !== false){
                //清空未读消息
                $this->clearMessage($user['id'], $type);
                $this -> setFd($ws, $user['id'], $fd, $type);
            }
            if (strpos($type, 'chatGroup_uid_') !== false){
                //如果是群聊链接
                $this->clearGroupMessage($user['id'], $type);
                $this -> setFd($ws, $user['id'], $fd, $type);
            }
            if ($type == 'index'){
                $this -> setFd($ws, $user['id'], $fd, $type);
                $this -> readDelay($ws, $user['id'], $fd);
            }
        }
    }

    //清空未读消息
    public function clearMessage($uid,$fid){
        $data = $this -> getSocket($uid);
        $fid = explode('chat_uid_', $fid)[1];
        unset($data['delay_list'][$fid]);
        $this -> redis -> set(config('redis.socket_pre') . $uid, $data);
    }

    //清空未读消息(群聊）
    public function clearGroupMessage($uid,$fid){
        $data = $this -> getSocket($uid);
        $fid = explode('chatGroup_uid_', $fid)[1];
        unset($data['delayGroup_list'][$fid]);
        $this -> redis -> set(config('redis.socket_pre') . $uid, $data);
    }
    //未在线时消息推送
    public function readDelay($ws, $uid, $fd){
        $data = $this -> getSocket($uid);
        if (!empty($data['apply_list'])){
            foreach ($data['apply_list'] as $key => $value){
                //判断好友是否还存在
                $user = $this -> user -> findByIdWithStatus($key);
                if (empty($user)){
                    unset($data['apply_list'][$key]);
                    continue;
                }
                switch ($value['type']) {
                    case 'addFriend':
                        $this->success($ws, $fd, [
                            'type' => 'addFriend',
                            'from' => $key,
                            'username' => $user['username'],
                            'message' => $value
                        ]);
                        break;
//                    case 'addGroup':
//                        $this->success($ws, $fd, [
//                            'type' => 'addGroup',
//                            'from' => $key,
//                            'username' => $user['username'],
//                            'message' => $value
//                        ]);
//                        break;
                }
            }
        }
        if (!empty($data['applyGroup_list'])){
            foreach ($data['applyGroup_list'] as $key => $value){
                //判断群聊是否还存在
                $group = $this -> groupModel -> find($key);
                foreach ($group['ids'] as $k => $v){
                    if ($v['type'] == 'root'){
                        $rootId = $k;
                    }
                }
                //获取群主信息
                $rootName = $this->user->getUsername($rootId);
                if (empty($group)){
                    unset($data['applyGroup_list'][$key]);
                    continue;
                }

                switch ($value['type']) {
                    case 'addGroup':
                        $this->success($ws, $fd, [
                            'type' => 'addGroup',
                            'from' => $key,
                            'username' => $rootName,
                            'message' => $value
                        ]);
                        break;
                }
            }
        }
        if (!empty($data['delay_list'])){
//            foreach ($data['delay_list'] as $key => $value){
//                $this -> success($ws, $fd, [
//                    'type' => 'chat',
//                    'uid' => $key,
//                    'count' => $value['count'],
//                    'message' => $value['message']
//                ]);
//                unset($data['delay_list'][$key]);
//            }
        }
//        $this -> redis -> set(config('redis.socket_pre') . $uid, $data);
    }

    public function setFd($ws, $uid, $fd, $type){
        $data = $this -> getSocket($uid);
        $data['fd'][$type] = $fd;
        foreach ($data['fd'] as $key => $value){
            $bindUid = $this -> getBindUid($ws, $value);
            if (empty($bindUid) || $bindUid != $uid){
                unset($data['fd'][$key]);
            }
        }
        $this -> redis -> set(config('redis.socket_pre') . $uid, $data);
    }

    public function getBindUid($ws, $fd){
        $info = $ws -> getClientInfo($fd);
        return empty($info['uid']) ? NULL : $info['uid'];
    }

    public function getSocket($uid){
        return $this -> redis -> get(config('redis.socket_pre') . $uid);
    }

    public function getUser($token){
        return $this -> redis -> get(config('redis.token_pre') . $token);
    }

    public function success($ws, $fd, $data){
        $this -> show($ws, $fd, config('status.success'), config('message.success'), $data);
    }

    public function fail($ws, $fd, $data){
        $this -> show($ws, $fd, config('status.error'), $data, NULL);
    }

    public function show($ws, $fd, $status, $message, $result){
        $data = [
            'status' => $status,
            'message' => $message,
            'data' => $result
        ];
        $ws -> push($fd, json_encode($data));
    }

}