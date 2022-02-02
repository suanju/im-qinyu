<?php

namespace server\business;


use app\common\model\api\User;
use server\base\Base;
use app\common\model\api\Chat as Model;
use app\common\model\api\ChatGroup as chatGroupModel;
use app\common\model\api\Group as groupModel;
use app\common\model\api\Collect as collectModel;
use app\common\business\lib\Str  as Str;
use app\common\model\api\Friend as friendModel;
use think\facade\Cache;


class Chat extends Base
{
    //心跳次数
    private $Maxheartbeat = 3;
    private $heartbeatStatus = true;

    public function switchboard($ws, $frame){
        $data = json_decode($frame -> data, true);
        //心跳归0
        $uid = $this -> getBindUid($ws, $frame->fd);
        $socket = $this -> getSocket($uid);
        $socket['heartbeat'] = 0 ;
        $this -> redis -> set(config('redis.socket_pre') .$uid, $socket);
        //判断消息类型
        switch ($data['type']){
            case 'addFriend' :
                $this -> addFriend($ws, $frame -> fd, $data);
                break;
            case 'addGroup' :
                $this -> addGroup($ws, $frame -> fd, $data);
                break;
            case 'chat' :
                $this -> chat($ws, $frame -> fd, $data);
                break;
            case 'chatGroup' :
                $this -> chatGroup($ws, $frame -> fd, $data);
                break;
            case 'heartbeat' :
                $this -> heartbeat($ws, $frame -> fd, $data);
                break;
        }
    }

    private function chat($ws, $fd, $data){
        $uid = $this -> getBindUid($ws, $fd);
        $chatModel = new Model();
        $collectModel = new collectModel();
        $userModel = new User();
        $friendModel = new  friendModel();
        $str = new Str();
        //获取fid的用户数据
        $fidInfo = $userModel->getPortrait($uid);
        //获取自己的用户数据
        $uidInfo = $userModel->getPortrait($uid);

        //判断是否为好友
        $isExist = $userModel->findByIdWithStatus($data['uid']);
        if (! $friendModel->isFriend($uid, $isExist['id'])) {
            $this->fail($ws, $fd, '请先添加为好友!');
        }else{
            //为好友发送消息
            switch ($data['messageType']){
                case 'text' :
                    //聊天消息为text
                    $chatModel-> save([
                        'uid' => $uid,
                        'fid' => $data['uid'],
                        'message' => $data['message'],
                        'messageType' => $data['messageType'],
                    ]);
                    $socket = $this -> getSocket($data['uid']);
                    //添加到最近里聊天
                    $userSocket = $this -> getSocket($uid);
                    //双向添加
                    $socket['latelyChat'][$uid] = ['message' => $data['message'],'messageType' => $data['messageType'],'time'=>$str->msectime()];
                    $userSocket['latelyChat'][$data['uid']] = ['message' => $data['message'],'messageType' => $data['messageType'],'time'=> $str->msectime()];
                    $this -> redis -> set(config('redis.socket_pre') . $data['uid'], $socket,3600*72);
                    $this -> redis -> set(config('redis.socket_pre') . $uid, $userSocket,3600*72);


                    if (!empty($socket['fd']['chat_uid_' . $uid])){
                        $this -> success($ws, $socket['fd']['chat_uid_' . $uid], [
                            'type' => 'chat',
                            'message' => $data['message'],
                            'messageType' => $data['messageType'],
                            'id' => $chatModel->id,
                            'uid' => $data['uid'],
                            'portrait' => $fidInfo,
                            'time'=> $str->msectime()
                        ]);
                    }else if (!empty($socket['fd']['index'])){
                        //在线未读同样添加
                        if (!empty($socket['delay_list'][$uid])){
                            $socket['delay_list'][$uid]['count'] += 1;
                            $socket['delay_list'][$uid]['message'] = $data['message'];
                        }else{
                            $socket['delay_list'][$uid] = [
                                'count' => 1,
                                'message' => $data['message'],
                                'messageType' => $data['messageType'],
                            ];
                        }
                        $this -> redis -> set(config('redis.socket_pre') . $data['uid'], $socket);

                        $this -> success($ws, $socket['fd']['index'], [
                            'type' => 'chat',
                            'uid' => $uid,
                            'message' => $data['message'],
                            'messageType' => $data['messageType'],
                            'time'=> $str->msectime()
                        ]);
                    }else{
                        if (!empty($socket['delay_list'][$uid])){
                            $socket['delay_list'][$uid]['count'] += 1;
                            $socket['delay_list'][$uid]['message'] = $data['message'];
                        }else{
                            $socket['delay_list'][$uid] = [
                                'count' => 1,
                                'message' => $data['message'],
                                'messageType' => $data['messageType'],
                            ];
                        }
                        $this -> redis -> set(config('redis.socket_pre') . $data['uid'], $socket);
                    }

                    break;
                case 'pic' :
                    //聊天消息为pic(图片）
                    $url = chatBase64ToFile($data['message']);

                    //存数据库
                    $chatModel-> save([
                        'uid' => $uid,
                        'fid' => $data['uid'],
                        'message' => $url,
                        'messageType' => $data['messageType'],
                    ]);
                    $socket = $this -> getSocket($data['uid']);
                    //添加到最近里聊天
                    $userSocket = $this -> getSocket($uid);
                    //双向添加
                    $socket['latelyChat'][$uid] = ['message' => '[ 图片 ]','messageType' => $data['messageType'],'time'=>$str->msectime()];
                    $userSocket['latelyChat'][$data['uid']] = ['message' => '[ 图片 ]','messageType' => $data['messageType'],'time'=> $str->msectime()];
                    $this -> redis -> set(config('redis.socket_pre') . $data['uid'], $socket,3600*72);
                    $this -> redis -> set(config('redis.socket_pre') . $uid, $userSocket,3600*72);


                    if (!empty($socket['fd']['chat_uid_' . $uid])){
                        $this -> success($ws, $socket['fd']['chat_uid_' . $uid], [
                            'type' => 'chat',
                            'message' => $url,
                            'messageType' => $data['messageType'],
                            'id' => $chatModel->id,
                            'uid' => $data['uid'],
                            'portrait' => $fidInfo,
                            'time'=> $str->msectime()
                        ]);
                    }else if (!empty($socket['fd']['index'])){
                        //在线未读同样添加
                        if (!empty($socket['delay_list'][$uid])){
                            $socket['delay_list'][$uid]['count'] += 1;
                            $socket['delay_list'][$uid]['message'] = '[ 图片 ]';
                        }else{
                            $socket['delay_list'][$uid] = [
                                'count' => 1,
                                'message' => '[ 图片 ]',
                                'messageType' => $data['messageType'],
                            ];
                        }
                        $this -> redis -> set(config('redis.socket_pre') . $data['uid'], $socket);

                        $this -> success($ws, $socket['fd']['index'], [
                            'type' => 'chat',
                            'uid' => $uid,
                            'message' =>'[ 图片 ]',
                            'messageType' => $data['messageType'],
                            'time'=> $str->msectime()
                        ]);
                    }else{
                        if (!empty($socket['delay_list'][$uid])){
                            $socket['delay_list'][$uid]['count'] += 1;
                            $socket['delay_list'][$uid]['message'] = '[ 图片 ]';
                        }else{
                            $socket['delay_list'][$uid] = [
                                'count' => 1,
                                'message' => '[ 图片 ]',
                                'messageType' => $data['messageType'],
                            ];
                        }
                        $this -> redis -> set(config('redis.socket_pre') . $data['uid'], $socket);
                    }

                    break;
                //语音消息
                case 'mic' :
                    //聊天消息为mic(语音）
                    $message = json_decode($data['message'],true);
                    $url = chatMicBase64ToFile($message['base64']);
                    $message['base64'] = $url;
                    $message = json_encode($message);

                    //存数据库
                    $chatModel-> save([
                        'uid' => $uid,
                        'fid' => $data['uid'],
                        'message' => $message,
                        'messageType' => $data['messageType'],
                    ]);
                    $socket = $this -> getSocket($data['uid']);
                    //添加到最近里聊天
                    $userSocket = $this -> getSocket($uid);
                    //双向添加
                    $socket['latelyChat'][$uid] = ['message' => '[ 语音 ]','messageType' => $data['messageType'],'time'=>$str->msectime()];
                    $userSocket['latelyChat'][$data['uid']] = ['message' => '[ 语音 ]','messageType' => $data['messageType'],'time'=> $str->msectime()];
                    $this -> redis -> set(config('redis.socket_pre') . $data['uid'], $socket,3600*72);
                    $this -> redis -> set(config('redis.socket_pre') . $uid, $userSocket,3600*72);


                    if (!empty($socket['fd']['chat_uid_' . $uid])){
                        $this -> success($ws, $socket['fd']['chat_uid_' . $uid], [
                            'type' => 'chat',
                            'message' => $message,
                            'messageType' => $data['messageType'],
                            'id' => $chatModel->id,
                            'uid' => $data['uid'],
                            'portrait' => $fidInfo,
                            'time'=> $str->msectime()
                        ]);
                    }else if (!empty($socket['fd']['index'])){
                        //在线未读同样添加
                        if (!empty($socket['delay_list'][$uid])){
                            $socket['delay_list'][$uid]['count'] += 1;
                            $socket['delay_list'][$uid]['message'] = '[ 语音 ]';
                        }else{
                            $socket['delay_list'][$uid] = [
                                'count' => 1,
                                'message' => '[ 语音 ]',
                                'messageType' => $data['messageType'],
                            ];
                        }
                        $this -> redis -> set(config('redis.socket_pre') . $data['uid'], $socket);

                        $this -> success($ws, $socket['fd']['index'], [
                            'type' => 'chat',
                            'uid' => $uid,
                            'message' =>'[ 语音 ]',
                            'messageType' => $data['messageType'],
                            'time'=> $str->msectime()
                        ]);
                    }else{
                        if (!empty($socket['delay_list'][$uid])){
                            $socket['delay_list'][$uid]['count'] += 1;
                            $socket['delay_list'][$uid]['message'] = '[ 语音 ]';
                        }else{
                            $socket['delay_list'][$uid] = [
                                'count' => 1,
                                'message' => '[ 语音 ]',
                                'messageType' => $data['messageType'],
                            ];
                        }
                        $this -> redis -> set(config('redis.socket_pre') . $data['uid'], $socket);
                    }

                    break;
                case 'collect' :
                    //聊天消息为collect(收藏）
                    $messageInfo = $collectModel->find($data['message']);
                    switch ($messageInfo->collect_type){
                        case  'text':
                            $easyMessage = $messageInfo->collect;
                            break;
                        case  'pic':
                            $easyMessage = '[ 图片 ]';
                            break;
                        case  'mic':
                            $easyMessage = '[ 语音 ]';
                            break;
                    }
                    //存数据库
                    $chatModel-> save([
                        'uid' => $uid,
                        'fid' => $data['uid'],
                        'message' => $messageInfo->collect,
                        'messageType' => $messageInfo->collect_type,
                    ]);
                    $socket = $this -> getSocket($data['uid']);
                    //添加到最近里聊天
                    $userSocket = $this -> getSocket($uid);
                    //双向添加
                    $socket['latelyChat'][$uid] = ['message' =>$easyMessage,'messageType' => $messageInfo->collect_type,'time'=>$str->msectime()];
                    $userSocket['latelyChat'][$data['uid']] = ['message' =>$easyMessage,'messageType' => $messageInfo->collect_type,'time'=> $str->msectime()];
                    $this -> redis -> set(config('redis.socket_pre') . $data['uid'], $socket,3600*72);
                    $this -> redis -> set(config('redis.socket_pre') . $uid, $userSocket,3600*72);


                    if (!empty($socket['fd']['chat_uid_' . $uid])){
                        $this -> success($ws, $socket['fd']['chat_uid_' . $uid], [
                            'type' => 'chat',
                            'message' => $messageInfo->collect,
                            'messageType' => $messageInfo->collect_type,
                            'id' => $chatModel->id,
                            'uid' => $data['uid'],
                            'portrait' => $fidInfo,
                            'time'=> $str->msectime()
                        ]);
                    }else if (!empty($socket['fd']['index'])){
                        //在线未读同样添加
                        if (!empty($socket['delay_list'][$uid])){
                            $socket['delay_list'][$uid]['count'] += 1;
                            $socket['delay_list'][$uid]['message'] = '[ 语音 ]';
                        }else{
                            $socket['delay_list'][$uid] = [
                                'count' => 1,
                                'message' => $easyMessage,
                                'messageType' => $messageInfo->collect_type,
                            ];
                        }
                        $this -> redis -> set(config('redis.socket_pre') . $data['uid'], $socket);

                        $this -> success($ws, $socket['fd']['index'], [
                            'type' => 'chat',
                            'uid' => $uid,
                            'message' =>$easyMessage,
                            'messageType' => $messageInfo->collect_type,
                            'time'=> $str->msectime()
                        ]);
                    }else{
                        if (!empty($socket['delay_list'][$uid])){
                            $socket['delay_list'][$uid]['count'] += 1;
                            $socket['delay_list'][$uid]['message'] = '[ 语音 ]';
                        }else{
                            $socket['delay_list'][$uid] = [
                                'count' => 1,
                                'message' => $easyMessage,
                                'messageType' => $messageInfo->collect_type,
                            ];
                        }
                        $this -> redis -> set(config('redis.socket_pre') . $data['uid'], $socket);
                    }

                    break;

                case 'map' :
                    //聊天消息为mic(语音）
                    $message = $data['message'];

                    //存数据库
                    $chatModel-> save([
                        'uid' => $uid,
                        'fid' => $data['uid'],
                        'message' => $message,
                        'messageType' => $data['messageType'],
                    ]);
                    $socket = $this -> getSocket($data['uid']);
                    //添加到最近里聊天
                    $userSocket = $this -> getSocket($uid);
                    //双向添加
                    $socket['latelyChat'][$uid] = ['message' => '[ 位置信息 ]','messageType' => $data['messageType'],'time'=>$str->msectime()];
                    $userSocket['latelyChat'][$data['uid']] = ['message' => '[ 位置信息 ]','messageType' => $data['messageType'],'time'=> $str->msectime()];
                    $this -> redis -> set(config('redis.socket_pre') . $data['uid'], $socket,3600*72);
                    $this -> redis -> set(config('redis.socket_pre') . $uid, $userSocket,3600*72);


                    if (!empty($socket['fd']['chat_uid_' . $uid])){
                        $this -> success($ws, $socket['fd']['chat_uid_' . $uid], [
                            'type' => 'chat',
                            'message' => $message,
                            'messageType' => $data['messageType'],
                            'id' => $chatModel->id,
                            'uid' => $data['uid'],
                            'portrait' => $fidInfo,
                            'time'=> $str->msectime()
                        ]);
                    }else if (!empty($socket['fd']['index'])){
                        //在线未读同样添加
                        if (!empty($socket['delay_list'][$uid])){
                            $socket['delay_list'][$uid]['count'] += 1;
                            $socket['delay_list'][$uid]['message'] = '[ 语音 ]';
                        }else{
                            $socket['delay_list'][$uid] = [
                                'count' => 1,
                                'message' => '[ 位置信息 ]',
                                'messageType' => $data['messageType'],
                            ];
                        }
                        $this -> redis -> set(config('redis.socket_pre') . $data['uid'], $socket);

                        $this -> success($ws, $socket['fd']['index'], [
                            'type' => 'chat',
                            'uid' => $uid,
                            'message' =>'[ 位置信息 ]',
                            'messageType' => $data['messageType'],
                            'time'=> $str->msectime()
                        ]);
                    }else{
                        if (!empty($socket['delay_list'][$uid])){
                            $socket['delay_list'][$uid]['count'] += 1;
                            $socket['delay_list'][$uid]['message'] = '[ 语音 ]';
                        }else{
                            $socket['delay_list'][$uid] = [
                                'count' => 1,
                                'message' => '[ 位置信息 ]',
                                'messageType' => $data['messageType'],
                            ];
                        }
                        $this -> redis -> set(config('redis.socket_pre') . $data['uid'], $socket);
                    }

                    break;

            }
        }

    }

    public function chatGroup($ws, $fd, $data){
        $uid = $this -> getBindUid($ws, $fd);
        $chatModel = new chatGroupModel();
        $groupModel = new groupModel();
        $groupData = $groupModel->where('id',$data['uid'])->find()->toArray();
        //成员中删除自己
        unset($groupData['ids'][$uid]);

        $collectModel = new collectModel();
        $userModel = new User();
        $str = new Str();
        //获取fid的用户数据
        $fidInfo = $userModel->getPortrait($uid);

        switch ($data['messageType']){
                case 'text' :
                    //聊天消息为text
                    $chatModel-> save([
                        'uid' => $uid,
                        'fid' => $data['uid'],
                        'message' => $data['message'],
                        'messageType' => $data['messageType'],
                    ]);
                    $userSocket = $this -> getSocket($uid);
                    $userSocket['latelyChatGroup'][$data['uid']] = ['message' => $data['message'],'messageType' => $data['messageType'],'time'=> $str->msectime()];
                    $this -> redis -> set(config('redis.socket_pre') . $uid, $userSocket,3600*72);
                    //群聊所有人添加最近聊天
                foreach ($groupData['ids'] as $k => $v){
                    $socket = $this -> getSocket($k);
                    //添加到最近里聊天
                    //添加到最近聊天
                    $socket['latelyChatGroup'][$data['uid']] = ['message' => $data['message'],'messageType' => $data['messageType'],'time'=>$str->msectime()];
                    $this -> redis -> set(config('redis.socket_pre') . $k, $socket,3600*72);

                    //推送消息方面
                    if (!empty($socket['fd']['chatGroup_uid_' . $data['uid']])){
                        $this -> success($ws, $socket['fd']['chatGroup_uid_' .$data['uid']], [
                            'type' => 'chatGroup',
                            'message' => $data['message'],
                            'messageType' => $data['messageType'],
                            'id' => $chatModel->id,
                            'uid' => $uid,
                            'portrait' => $fidInfo,
                            'time'=> $str->msectime()
                        ]);
                    }else if (!empty($socket['fd']['index'])){
                        //在线未读同样添加
                        if (!empty($socket['delayGroup_list'][$data['uid']])){
                            $socket['delayGroup_list'][$data['uid']]['count'] += 1;
                            $socket['delayGroup_list'][$data['uid']]['message'] = $data['message'];
                        }else{
                            $socket['delayGroup_list'][$data['uid']] = [
                                'count' => 1,
                                'message' => $data['message'],
                                'messageType' => $data['messageType'],
                            ];
                        }
                        $this -> redis -> set(config('redis.socket_pre') . $k, $socket);

                        $this -> success($ws, $socket['fd']['index'], [
                            'type' => 'chatGroup',
                            'uid' => $data['uid'],
                            'message' => $data['message'],
                            'messageType' => $data['messageType'],
                            'time'=> $str->msectime()
                        ]);
                    }else{
                        if (!empty($socket['delayGroup_list'][$data['uid']])){
                            $socket['delayGroup_list'][$data['uid']]['count'] += 1;
                            $socket['delayGroup_list'][$data['uid']]['message'] = $data['message'];
                        }else{
                            $socket['delayGroup_list'][$data['uid']] = [
                                'count' => 1,
                                'message' => $data['message'],
                                'messageType' => $data['messageType'],
                            ];
                        }
                        $this -> redis -> set(config('redis.socket_pre') . $k, $socket);
                    }
                }


                    break;
                case 'pic' :
                    //聊天消息为pic(图片）
                    $url = chatBase64ToFile($data['message']);

                    //存数据库
                    $chatModel-> save([
                        'uid' => $uid,
                        'fid' => $data['uid'],
                        'message' => $url,
                        'messageType' => $data['messageType'],
                    ]);
                    $userSocket = $this -> getSocket($uid);
                    $userSocket['latelyChatGroup'][$data['uid']] = ['message' =>  '[ 图片 ]','messageType' => $data['messageType'],'time'=> $str->msectime()];
                    $this -> redis -> set(config('redis.socket_pre') . $uid, $userSocket,3600*72);
                    //群聊所有人添加最近聊天
                    foreach ($groupData['ids'] as $k => $v){
                        $socket = $this -> getSocket($k);
                        //添加到最近里聊天
                        //添加到最近聊天
                        $socket['latelyChatGroup'][$data['uid']] = ['message' =>'[ 图片 ]','messageType' => $data['messageType'],'time'=>$str->msectime()];
                        $this -> redis -> set(config('redis.socket_pre') . $k, $socket,3600*72);

                        //推送消息方面
                        if (!empty($socket['fd']['chatGroup_uid_' . $data['uid']])){
                            $this -> success($ws, $socket['fd']['chatGroup_uid_' .$data['uid']], [
                                'type' => 'chatGroup',
                                'message' => '[ 图片 ]',
                                'messageType' => $data['messageType'],
                                'id' => $chatModel->id,
                                'uid' => $uid,
                                'portrait' => $fidInfo,
                                'time'=> $str->msectime()
                            ]);
                        }else if (!empty($socket['fd']['index'])){
                            //在线未读同样添加
                            if (!empty($socket['delayGroup_list'][$data['uid']])){
                                $socket['delayGroup_list'][$data['uid']]['count'] += 1;
                                $socket['delayGroup_list'][$data['uid']]['message'] = '[ 图片 ]';
                            }else{
                                $socket['delayGroup_list'][$data['uid']] = [
                                    'count' => 1,
                                    'message' => '[ 图片 ]',
                                    'messageType' => $data['messageType'],
                                ];
                            }
                            $this -> redis -> set(config('redis.socket_pre') . $k, $socket);

                            $this -> success($ws, $socket['fd']['index'], [
                                'type' => 'chatGroup',
                                'uid' => $data['uid'],
                                'message' => '[ 图片 ]',
                                'messageType' => $data['messageType'],
                                'time'=> $str->msectime()
                            ]);
                        }else{
                            if (!empty($socket['delayGroup_list'][$data['uid']])){
                                $socket['delayGroup_list'][$data['uid']]['count'] += 1;
                                $socket['delayGroup_list'][$data['uid']]['message'] = '[ 图片 ]';
                            }else{
                                $socket['delayGroup_list'][$data['uid']] = [
                                    'count' => 1,
                                    'message' => '[ 图片 ]',
                                    'messageType' => $data['messageType'],
                                ];
                            }
                            $this -> redis -> set(config('redis.socket_pre') . $k, $socket);
                        }
                    }
                    break;
                //语音消息
                case 'mic' :
                    //聊天消息为mic(语音）
                    $message = json_decode($data['message'],true);
                    $url = chatMicBase64ToFile($message['base64']);
                    $message['base64'] = $url;
                    $message = json_encode($message);

                    //存数据库
                    $chatModel-> save([
                        'uid' => $uid,
                        'fid' => $data['uid'],
                        'message' => $message,
                        'messageType' => $data['messageType'],
                    ]);
                    $userSocket = $this -> getSocket($uid);
                    $userSocket['latelyChatGroup'][$data['uid']] = ['message' =>  '[ 语音 ]','messageType' => $data['messageType'],'time'=> $str->msectime()];
                    $this -> redis -> set(config('redis.socket_pre') . $uid, $userSocket,3600*72);
                    //群聊所有人添加最近聊天
                    foreach ($groupData['ids'] as $k => $v){
                        $socket = $this -> getSocket($k);
                        //添加到最近里聊天
                        //添加到最近聊天
                        $socket['latelyChatGroup'][$data['uid']] = ['message' =>'[ 语音 ]','messageType' => $data['messageType'],'time'=>$str->msectime()];
                        $this -> redis -> set(config('redis.socket_pre') . $k, $socket,3600*72);

                        //推送消息方面
                        if (!empty($socket['fd']['chatGroup_uid_' . $data['uid']])){
                            $this -> success($ws, $socket['fd']['chatGroup_uid_' .$data['uid']], [
                                'type' => 'chatGroup',
                                'message' => '[ 语音 ]',
                                'messageType' => $data['messageType'],
                                'id' => $chatModel->id,
                                'uid' => $uid,
                                'portrait' => $fidInfo,
                                'time'=> $str->msectime()
                            ]);
                        }else if (!empty($socket['fd']['index'])){
                            //在线未读同样添加
                            if (!empty($socket['delayGroup_list'][$data['uid']])){
                                $socket['delayGroup_list'][$data['uid']]['count'] += 1;
                                $socket['delayGroup_list'][$data['uid']]['message'] = '[ 语音 ]';
                            }else{
                                $socket['delayGroup_list'][$data['uid']] = [
                                    'count' => 1,
                                    'message' => '[ 语音 ]',
                                    'messageType' => $data['messageType'],
                                ];
                            }
                            $this -> redis -> set(config('redis.socket_pre') . $k, $socket);

                            $this -> success($ws, $socket['fd']['index'], [
                                'type' => 'chatGroup',
                                'uid' => $data['uid'],
                                'message' => '[ 语音 ]',
                                'messageType' => $data['messageType'],
                                'time'=> $str->msectime()
                            ]);
                        }else{
                            if (!empty($socket['delayGroup_list'][$data['uid']])){
                                $socket['delayGroup_list'][$data['uid']]['count'] += 1;
                                $socket['delayGroup_list'][$data['uid']]['message'] = '[ 语音 ]';
                            }else{
                                $socket['delayGroup_list'][$data['uid']] = [
                                    'count' => 1,
                                    'message' => '[ 语音 ]',
                                    'messageType' => $data['messageType'],
                                ];
                            }
                            $this -> redis -> set(config('redis.socket_pre') . $k, $socket);
                        }
                    }
                    break;
                case 'collect' :
                    //聊天消息为collect(收藏）
                    $messageInfo = $collectModel->find($data['message']);
                    switch ($messageInfo->collect_type){
                        case  'text':
                            $easyMessage = $messageInfo->collect;
                            break;
                        case  'pic':
                            $easyMessage = '[ 图片 ]';
                            break;
                        case  'mic':
                            $easyMessage = '[ 语音 ]';
                            break;
                    }
                    //存数据库
                    $chatModel-> save([
                        'uid' => $uid,
                        'fid' => $data['uid'],
                        'message' => $messageInfo->collect,
                        'messageType' => $messageInfo->collect_type,
                    ]);
                    $userSocket = $this -> getSocket($uid);
                    $userSocket['latelyChatGroup'][$data['uid']] = ['message' => $easyMessage,'messageType' => $messageInfo->collect_type,'time'=> $str->msectime()];
                    $this -> redis -> set(config('redis.socket_pre') . $uid, $userSocket,3600*72);
                    //群聊所有人添加最近聊天
                    foreach ($groupData['ids'] as $k => $v){
                        $socket = $this -> getSocket($k);
                        //添加到最近里聊天
                        //添加到最近聊天
                        $socket['latelyChatGroup'][$data['uid']] = ['message' =>$easyMessage,'messageType' => $messageInfo->collect_type,'time'=>$str->msectime()];
                        $this -> redis -> set(config('redis.socket_pre') . $k, $socket,3600*72);

                        //推送消息方面
                        if (!empty($socket['fd']['chatGroup_uid_' . $data['uid']])){
                            $this -> success($ws, $socket['fd']['chatGroup_uid_' .$data['uid']], [
                                'type' => 'chatGroup',
                                'message' => $easyMessage,
                                'messageType' => $messageInfo->collect_type,
                                'id' => $chatModel->id,
                                'uid' => $uid,
                                'portrait' => $fidInfo,
                                'time'=> $str->msectime()
                            ]);
                        }else if (!empty($socket['fd']['index'])){
                            //在线未读同样添加
                            if (!empty($socket['delayGroup_list'][$data['uid']])){
                                $socket['delayGroup_list'][$data['uid']]['count'] += 1;
                                $socket['delayGroup_list'][$data['uid']]['message'] = '[ 语音 ]';
                            }else{
                                $socket['delayGroup_list'][$data['uid']] = [
                                    'count' => 1,
                                    'message' => $easyMessage,
                                    'messageType' => $messageInfo->collect_type,
                                ];
                            }
                            $this -> redis -> set(config('redis.socket_pre') . $k, $socket);

                            $this -> success($ws, $socket['fd']['index'], [
                                'type' => 'chatGroup',
                                'uid' => $data['uid'],
                                'message' => $easyMessage,
                                'messageType' => $messageInfo->collect_type,
                                'time'=> $str->msectime()
                            ]);
                        }else{
                            if (!empty($socket['delayGroup_list'][$data['uid']])){
                                $socket['delayGroup_list'][$data['uid']]['count'] += 1;
                                $socket['delayGroup_list'][$data['uid']]['message'] = $easyMessage;
                            }else{
                                $socket['delayGroup_list'][$data['uid']] = [
                                    'count' => 1,
                                    'message' => $easyMessage,
                                    'messageType' => $messageInfo->collect_type,
                                ];
                            }
                            $this -> redis -> set(config('redis.socket_pre') . $k, $socket);
                        }
                    }
                    break;

                case 'map' :
                    //聊天消息为mic(语音）
                    $message = $data['message'];

                    //存数据库
                    $chatModel-> save([
                        'uid' => $uid,
                        'fid' => $data['uid'],
                        'message' => $message,
                        'messageType' => $data['messageType'],
                    ]);
                    $userSocket = $this -> getSocket($uid);
                    $userSocket['latelyChatGroup'][$data['uid']] = ['message' =>  '[ 位置信息 ]','messageType' => $data['messageType'],'time'=> $str->msectime()];
                    $this -> redis -> set(config('redis.socket_pre') . $uid, $userSocket,3600*72);
                    //群聊所有人添加最近聊天
                    foreach ($groupData['ids'] as $k => $v){
                        $socket = $this -> getSocket($k);
                        //添加到最近里聊天
                        //添加到最近聊天
                        $socket['latelyChatGroup'][$data['uid']] = ['message' =>'[ 位置信息 ]','messageType' => $data['messageType'],'time'=>$str->msectime()];
                        $this -> redis -> set(config('redis.socket_pre') . $k, $socket,3600*72);

                        //推送消息方面
                        if (!empty($socket['fd']['chatGroup_uid_' . $data['uid']])){
                            $this -> success($ws, $socket['fd']['chatGroup_uid_' .$data['uid']], [
                                'type' => 'chatGroup',
                                'message' => '[ 位置信息 ]',
                                'messageType' => $data['messageType'],
                                'id' => $chatModel->id,
                                'uid' => $uid,
                                'portrait' => $fidInfo,
                                'time'=> $str->msectime()
                            ]);
                        }else if (!empty($socket['fd']['index'])){
                            //在线未读同样添加
                            if (!empty($socket['delayGroup_list'][$data['uid']])){
                                $socket['delayGroup_list'][$data['uid']]['count'] += 1;
                                $socket['delayGroup_list'][$data['uid']]['message'] = '[ 位置信息 ]';
                            }else{
                                $socket['delayGroup_list'][$data['uid']] = [
                                    'count' => 1,
                                    'message' => '[ 位置信息 ]',
                                    'messageType' => $data['messageType'],
                                ];
                            }
                            $this -> redis -> set(config('redis.socket_pre') . $k, $socket);

                            $this -> success($ws, $socket['fd']['index'], [
                                'type' => 'chatGroup',
                                'uid' => $data['uid'],
                                'message' => '[ 位置信息 ]',
                                'messageType' => $data['messageType'],
                                'time'=> $str->msectime()
                            ]);
                        }else{
                            if (!empty($socket['delayGroup_list'][$data['uid']])){
                                $socket['delayGroup_list'][$data['uid']]['count'] += 1;
                                $socket['delayGroup_list'][$data['uid']]['message'] = '[ 位置信息 ]';
                            }else{
                                $socket['delayGroup_list'][$data['uid']] = [
                                    'count' => 1,
                                    'message' => '[ 位置信息 ]',
                                    'messageType' => $data['messageType'],
                                ];
                            }
                            $this -> redis -> set(config('redis.socket_pre') . $k, $socket);
                        }
                    }
                    break;

            }
    }

    private function addFriend($ws, $fd, $data)
    {
        $userModel = new User();
        $socket = $this->getSocket($data['target']);
        $socket['apply_list'][$data['uid']] = [
            'type' => 'addFriend',
            'message' => $data['message'],
            'remark' => $data['remark'],
            'portrait' => $userModel->getPortrait($data['uid']),
            'state' => 0];
        if (!empty($socket['fd']['index'])) {
            $this->success($ws, $socket['fd']['index'], [
                'type' => 'addFriend',
                'from' => $data['uid'],
                'username' => $data['username'],
                'message' => ['message' => $data['message'], 'remark' => $data['remark'], 'portrait' => $userModel->getPortrait($data['uid'])],
                'state' => 0
            ]);
        }
        $this->redis->set(config('redis.socket_pre') . $data['target'], $socket);
        $this->success($ws, $fd, NULL);
    }


    private function addGroup($ws, $fd, $data)
    {
        $userModel = new User();
        $socket = $this->getSocket($data['target']);
        $socket['applyGroup_list'][$data['groupId']] = [
            'type' => 'addGroup',
            'message' => $data['message'],
            'groupId'=>$data['groupId'],
            'portrait' => $userModel->getPortrait($data['uid']),
            'state' => 0];
        if (!empty($socket['fd']['index'])) {
            $this->success($ws, $socket['fd']['index'], [
                'type' => 'addGroup',
                'from' => $data['uid'],
                'username' => $data['username'],
                'message' => ['message' => $data['message'],'groupId'=>$data['groupId'], 'portrait' => $userModel->getPortrait($data['uid'])],
                'state' => 0
            ]);
        }
        $this->redis->set(config('redis.socket_pre') . $data['target'], $socket);
        $this->success($ws, $fd, NULL);
    }

    public function heartbeat($ws, $fd, $data){
        $this->timer($ws, $fd, $data);
        $this->success($ws, $fd, $data);
    }


    public function timer($ws, $fd, $data) {
        # 还有，强制心跳定时器只能触发一次，否则会出现生成多个定时器的情况
        if ($this->heartbeatStatus) {
            $this->heartbeatStatus = false;
            $obj = $this;
            swoole_timer_tick(10000, function ($timer_id) use (&$obj) {
                # 所有人的心跳次数+1
                $data = $this -> redis -> keys(config('redis.socket_pre').'*');
                foreach ($data as $socketId){
                    //redis设置心跳
                    $socket = $this -> redis -> get($socketId);
                    if (isset($socket['heartbeat'])){
                        $socket['heartbeat'] =  $socket['heartbeat'] + 1;
                        //判断是否超过最大丛连次数
                        if ($socket['heartbeat'] > $this->Maxheartbeat){
                            if (isset($socket['fd']['index'])){
                                unset($socket['fd']['index']);
                                $socket['heartbeat'] = 0;
                            }
                        }
                    }else{
                        $socket['heartbeat'] = 1;
                    }
                    $this->redis->set($socketId, $socket);
                }
            });
        }
    }

}