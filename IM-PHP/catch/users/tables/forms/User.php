<?php
namespace catchAdmin\users\tables\forms;

use catchAdmin\permissions\model\Department as DepartmentModel;
use catchAdmin\permissions\model\Job;
use catchAdmin\permissions\model\Roles;
use catcher\library\form\Form;

class User extends Form
{
    public function fields(): array
    {
        // TODO: Implement fields() method.
        return [
            self::input('username', '昵称')->col(self::col(12))->clearable(true)->required(),
            self::email('email', '邮箱')->col(self::col(12))->required()->clearable(true),
            self::input('password', '密码')
                ->placeholder('请输入密码')->clearable(true),
        ];
    }

}