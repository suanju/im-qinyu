<?php

namespace app\common\business\api;

use app\common\model\api\Group as groupModel;
use app\common\model\api\MyGroup as myGroupModel;
use app\common\model\api\User as userModel;
use app\common\business\api\User as userBusiness;
use think\cache\driver\Redis;
use think\Exception;
use WebSocket\Client;

class Group
{
    private $groupModel = NULL;
    private $myGroupModel = NULL;
    private $userModel = NUll;
    private $userBusiness = NUll;
    private $redis = NUll;


    public function __construct()
    {
        $this->groupModel = new groupModel();
        $this->myGroupModel = new myGroupModel();
        $this->userModel = new userModel();
        $this->userBusiness = new userBusiness();
        $this->redis = new Redis();
    }

    //创建群聊
    public function crateGroup($data)
    {
        //随机头像
        $isExist = $this->userModel->findByUserName($data['name']);
        $isGroupExist = $this->groupModel->findByGroupName($data['name']);
        if (!empty($isExist) || !empty($isGroupExist)) {
            throw new Exception("名字被用烂了");
        }
        $data['portrait'] = '/static/appStatic/userPortrait/system/auto' . rand(1, 10) . '.png';
        $list = [];
        for ($i = 0; $i < count($data['ids']); $i++) {
            $list[$data['ids'][$i]] = ['type' => 'member', 'time' => time()];
        }
        //加上群主
        $list[$data['uid']] = ['type' => 'root', 'time' => time()];
        $data['ids'] = $list;
        $state = $this->groupModel->save($data);
        // 给每个成员加上进入的群
        $myList = [];
        foreach ($list as $k => $v) {
            $setDate['uid'] = $k;
            $setDate['group_id'] = $this->groupModel->id;
            $myList[] = $setDate;
        }
        $this->myGroupModel->saveAll($myList);
        return $state;

    }

    //获取群聊
    public function getGroup($data)
    {
        return $this->myGroupModel->getGroup($data['uid']);
    }

    //获取群聊信息
    public function getGroupInfo($data)
    {
        $info = $this->groupModel->getGroupInfo($data['id'])->toArray();
        if ($info['ids'][$data['uid']]['type'] == 'root') {
            $info['position'] = '群主';
        } else {
            $info['position'] = '成员';
        }
        foreach ($info['ids'] as $k => $v) {
            $info['ids'][$k]['name'] = $this->userModel->getUsername($k);
            $info['ids'][$k]['portrait'] = $this->userModel->getPortrait($k);
        }

        return $info;
    }

    //更新群宣言
    public function setManifesto($data)
    {
        $info = $this->groupModel->find($data['id']);
        if ($info['ids'][$data['uid']]['type'] == 'root') {
            $info->manifesto = $data['manifesto'];
            return $info->save();
        } else {
            throw new Exception('您没有权限唷');
        }

    }

    //删除群聊
    public function delGroup($data)
    {
        $info = $this->groupModel->find($data['id']);
        if ($info['ids'][$data['uid']]['type'] == 'root') {
            //删除每个成员
            foreach ($info['ids'] as $k => $v) {
                $this->myGroupModel->where('uid', $k)->where('group_id', $data['id'])->delete();
                $this->userBusiness->delLatelyChatGroup(['uid' => $k, 'id' => $data['id']]);
            }
            //
            $state = $this->groupModel->where('id', $data['id'])->delete();
            return $state;
        } else {
            throw new Exception('您没有权限唷');
        }

    }

    //退出群聊
    public function exitGroup($data)
    {
        $info = $this->groupModel->find($data['id']);
        $idsInfo = $info['ids'];
        unset($idsInfo[$data['uid']]);
        $info->ids = $idsInfo;
        $info->save();
        $state = $this->myGroupModel->where('uid', $data['uid'])->where('group_id', $data['id'])->delete();
        return $state;
    }

    //踢出群聊
    public function delMember($data)
    {
        //不能移除自己（群主）
        if ($data['ids'] == $data['uid']) {
            throw new Exception('不能移除自己！');
        }
        $info = $this->groupModel->find($data['id']);
        if ($info['ids'][$data['uid']]['type'] == 'root') {
            //删除群中id
            $idsInfo = $info['ids'];
            unset($idsInfo[$data['ids']]);
            $info->ids = $idsInfo;
            $info->save();
            //群成员本身删除
            $this->myGroupModel->where('uid', $data['ids'])->where('group_id', $data['id'])->delete();

            //返回可用的ids
            foreach ($idsInfo as $k => $v) {
                $idsInfo[$k]['name'] = $this->userModel->getUsername($k);
                $idsInfo[$k]['portrait'] = $this->userModel->getPortrait($k);
            }
            return $idsInfo;
        } else {
            throw new Exception('您没有权限唷');
        }
    }

    //邀请好友加入群聊
    public function invitationGroup($data)
    {
        $info = $this->groupModel->find($data['id']);
        //数据库判断

        $userInfo = $this->userModel->find($data['uid']);
        if ($info['ids'][$data['uid']]['type'] == 'root') {
            for ($i = 0; $i < count($data['ids']); $i++) {
                $name = $this->userModel->getUsername($data['ids'][$i]);
                if (in_array($data['ids'][$i], $info['ids'])) {
                    throw new Exception($name . ' 已在群聊中');
                }
                $socket = $this->redis->get(config('redis.socket_pre') . $data['ids'][$i]);
                //redis判断
                if (!empty($socket['applyGroup_list'][$data['id']])) {
                    throw new Exception($name . ' 等待对方处理中！');
                }
                $send = [
                    'type' => 'addGroup',
                    'uid' => $data['uid'],
                    'username' => $userInfo['username'],
                    'groupId' => $data['id'],
                    'target' => $data['ids'][$i],
                    'message' => '邀请你加入' . $info['name'],
                ];
                $client = new Client(config('config.ws_host') . '?type=public&token=' . $data['token']);
                $client->send(json_encode($send));
                $client->close();
            }
            return '发送成功';
        } else {
            throw new Exception('您没有权限唷');
        }
    }

    //搜索群聊
    public function searchGroup($data)
    {
        //要获取的字段信息
        $field = ['id', 'name', 'portrait','ids'];
        $groupList = $this->groupModel->where('name', $data['value'])->field($field)->select()->toArray();
        if (empty($groupList)) {
            $groupList = $this->groupModel->where('name', 'like', '%' . $data['value'] . '%')->field($field)->limit(5)->select()->toArray();;
        }
        foreach ($groupList as $k => $v){
            foreach ($v['ids'] as $vk => $vv) {
                if ($vv['type'] == 'root') {
                    $rootId = $vk;
                }
            }
            $groupList[$k]['rootInfo'] = $this->userModel->getUserInfoByID($rootId,['id','username','portrait','email']);
        }
        return $groupList;

    }

    //更新头像
    public function updatePortrait($uid, $id, $file)
    {
        //获取群聊信息
        $groupInfo = $this->groupModel->find($id);
        foreach ($groupInfo['ids'] as $k => $v) {
            if ($v['type'] == 'root') {
                $rootId = $k;
            }
        }
        if ($uid != $rootId) {
            throw new Exception('只有群主才可更换');
        }

        $saveName = \think\facade\Filesystem::disk('groupPortrait')->putFile('group', $file, 'md5');
        $saveName = '/static/appStatic/groupPortrait/' . $saveName;
        $groupInfo->portrait = $saveName;
        $groupInfo->save();
        return $saveName;
    }

    //添加群聊
    public function addGroup($data)
    {
        $isExist = $this->groupModel->where('name',$data['name'])->find();
        if (empty($isExist)) {
            throw new Exception("群聊不存在！");
        }
        foreach ($isExist['ids'] as $k => $v){
            if ($k == $data['id']){
                throw new Exception("已加入！");
            }
        }
        //不需要管理审核完
        $idsAdd = $isExist->ids;
        $idsAdd[$data['id']] = ['type' =>'member', 'time' => time()];
        $isExist->ids = $idsAdd;
        $saveState =  $isExist->save();
        $addGroup = [
            'uid'=>$data['id'],
            'group_id'=>$isExist['id']
        ];
        $addState =  $this->myGroupModel->save($addGroup);
        return $saveState&&$addState;
    }


}