<?php

namespace catchAdmin\group\model;

use catchAdmin\users\model\User;
use think\Model;
use catcher\traits\db\BaseOptionsTrait;
use catcher\traits\db\ScopeTrait;
use catchAdmin\group\model\search\GroupSearch;
/**
 *
 * @property int $id
 * @property string $ids
 * @property string $name
 * @property string $manifesto
 * @property string $portrait
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 */
class Group extends Model
{
    use BaseOptionsTrait, ScopeTrait;
    use GroupSearch;
    
    public $field = [
        //
        'id',
        //
        'ids',
        // 群名称
        'name',
        // 群公告
        'manifesto',
        // 群头像
        'portrait',
        //
        'created_at',
        // 更新时间
        'updated_at',
        // 删除时间
        'deleted_at',
    ];
    
    public $name = 'api_group';


    public function getList()
    {
        $data = $this->catchSearch()
            ->catchOrder()
            ->paginate();

        //人数统计
        foreach ($data as $k => $v){
            $ids = json_decode($v['ids'],true);
            //群聊人数
            $data[$k]['idsNum'] = count($ids);
            //获取群主资料
            foreach ($ids as $idsKey => $idsValue){
                if ($idsValue['type'] == 'root'){
                    $data[$k]['rootId'] = $idsKey;
                }
            }
            $data[$k]['rootName'] = (new User)->getUsernameById($data[$k]['rootId']);
        }

        return $data;
    }


    //获取列表成员
    public function getGroupMembers($name){
        $Group = $this->where('name',$name)->find();
        $idsInfo = [];
        foreach (json_decode($Group['ids'],true) as $k => $v){
            $user =   (new User)->find($k);
            $idsInfo[$k]['id'] = $user['id'];
            $idsInfo[$k]['username'] = $user['username'];
            $idsInfo[$k]['email'] = $user['email'];
        }
        return $idsInfo;
    }

}