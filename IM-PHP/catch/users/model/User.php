<?php

namespace catchAdmin\users\model;

use catchAdmin\users\model\search\UserSearch;
use catcher\base\CatchModel;
use think\Model;
use catcher\traits\db\BaseOptionsTrait;
use catcher\traits\db\ScopeTrait;

/**
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $email
 * @property string $password_salt
 * @property string $last_login_token
 * @property string $portrait
 * @property int $status
 * @property int $create_time
 * @property int $update_time
 */
class User extends CatchModel
{
    use BaseOptionsTrait, ScopeTrait;
    use UserSearch;


    public $field = [
        //
        'id',
        // 用户名
        'username',
        // 密码
        'password',
        //
        'email',
        // 密码盐
        'password_salt',
        // 上次登录Token
        'last_login_token',
        // 头像
        'portrait',
        // 状态
        'status',
        // 注册时间
        'created_at',
        // 更新时间
        'updated_at',
        //
        'deleted_at'

    ];

    public $name = 'api_user';


    public function getList()
    {
        // 不分页
        if (property_exists($this, 'paginate') && $this->paginate === false) {
            return $this->withoutField(['password_salt', 'password', 'remember_token'], true)
                ->catchSearch()
                ->catchOrder()
                ->select();
        }

        // 分页列表
        return $this->withoutField(['password_salt', 'password', 'remember_token'], true)
            ->catchSearch()
            ->catchOrder()
            ->paginate();
    }

    public function updateBy($id, $data, string $field = ''):bool
    {
        $userInfo = $this->find($id);
        //没有更改密码就不更改
        if (!empty($data['password'])){
            $data['password'] = md5($userInfo['password_salt'] . $data['password'] . $userInfo['password_salt']);
        }else{
            unset($data['password']);
            unset($data['portrait']);
        }
        if (static::update($this->filterData($data), [$field ? : $this->getPk() => $id], $this->field)) {

            $this->updateChildren($id, $data);

            return true;
        }

        return false;
    }

    //获取名字
    public function getUsernameById($id){
        $data = $this->find($id);
        return $data['username'];
    }
}