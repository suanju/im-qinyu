<?php

use think\migration\Migrator;
use think\migration\db\Column;
use Phinx\Db\Adapter\MysqlAdapter;

class AdminApiUsers extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('admin_api_users', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci', 'comment' => '用户表' ,'id' => 'id' ,'primary_key' => ['id']]);
        $table->addColumn('username', 'string', ['limit' => 255,'null' => false,'default' => '','signed' => true,'comment' => '用户名',])
			->addColumn('password', 'char', ['limit' => 32,'null' => false,'default' => '','signed' => true,'comment' => '密码',])
			->addColumn('email', 'string', ['limit' => 40,'null' => true,'signed' => true,'comment' => '',])
			->addColumn('password_salt', 'string', ['limit' => 4,'null' => false,'default' => '','signed' => true,'comment' => '密码盐',])
			->addColumn('last_login_token', 'text', ['limit' => MysqlAdapter::TEXT_REGULAR,'null' => true,'signed' => true,'comment' => '上次登录Token',])
			->addColumn('portrait', 'text', ['limit' => MysqlAdapter::TEXT_REGULAR,'null' => true,'signed' => true,'comment' => '头像',])
			->addColumn('status', 'integer', ['limit' => MysqlAdapter::INT_REGULAR,'null' => true,'signed' => true,'comment' => '状态',])
			->addColumn('create_time', 'integer', ['limit' => MysqlAdapter::INT_REGULAR,'null' => true,'signed' => true,'comment' => '注册时间',])
			->addColumn('update_time', 'integer', ['limit' => MysqlAdapter::INT_REGULAR,'null' => true,'signed' => true,'comment' => '更新时间',])
			->addIndex(['username'], ['name' => 'admin_api_users_username'])
            ->create();
    }
}
