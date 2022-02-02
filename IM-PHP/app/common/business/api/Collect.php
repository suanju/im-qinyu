<?php

namespace app\common\business\api;
use app\common\model\api\Chat as ChatModel;
use app\common\model\api\Collect as CollectModel;
use app\common\model\api\ChatGroup as chatGroupModel;
use think\Exception;

class Collect
{
    private $chatModel = NULL;
    private $collectModel = NULL;
    private $chatGroupModel = NULL;

    public function __construct()
    {
        $this->chatModel = new ChatModel();
        $this->collectModel = new CollectModel();
        $this->chatGroupModel = new chatGroupModel();

    }

    //收藏消息(好友）
    public function setCollect($data){
        $isCollect = $this->collectModel->where('message_id',$data['messageId'])->where('message_type','friend')->where('uid',$data['uid'])->find();
        if (!empty($isCollect)){
            throw new Exception('已收藏！');
        }
        $messageInfo = $this->chatModel->find($data['messageId']);
        if ($messageInfo['messageType'] == 'map'){
            throw new Exception('位置信息不可收藏');
        }
        $collect = [
            'uid' => $data['uid'],
            'send_id' => $messageInfo['uid'],
            'collect' => $messageInfo['message'],
            'collect_type' => $messageInfo['messageType'],
            'message_id' => $data['messageId'],
            'message_type' => 'friend',
            'send_time' => $messageInfo['create_time'],
        ];
        $state = $this->collectModel->save($collect);
        return $state;

    }

    //收藏消息(群聊）
    public function setCollectGroup($data){
        $isCollect = $this->collectModel->where('message_id',$data['messageId'])->where('message_type','group')->where('uid',$data['uid'])->find();
        if (!empty($isCollect)){
            throw new Exception('已收藏！');
        }
        $messageInfo = $this->chatGroupModel->find($data['messageId']);
        if ($messageInfo['messageType'] == 'map'){
            throw new Exception('位置信息不可收藏');
        }
        $collect = [
            'uid' => $data['uid'],
            'send_id' => $messageInfo['uid'],
            'collect' => $messageInfo['message'],
            'collect_type' => $messageInfo['messageType'],
            'message_id' => $data['messageId'],
            'message_type' => 'group',
            'send_time' => $messageInfo['create_time'],
        ];
        $state = $this->collectModel->save($collect);
        return $state;

    }


    //获取收藏
    public function getCollect($data){
       $info = $this->collectModel->getCollect($data['uid']);

       return $info;
    }

    //删除收藏
    public function delCollect($data){
        $info = $this->collectModel->where('id',$data['id'])->find();
        if ($info['uid'] != $data['uid']){
           throw new Exception('非法操作');
        }
        $state = $info->delete();

        return $state;

    }

}