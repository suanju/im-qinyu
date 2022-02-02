<?php

namespace catchAdmin\dynamic\model;

use think\Model;
use catcher\traits\db\BaseOptionsTrait;
use catcher\traits\db\ScopeTrait;
/**
 *
 * @property int $id
 * @property int $uid
 * @property string $content
 * @property string $img
 * @property string $likes
 * @property string $comments
 * @property int $created_at
 */
class Dynamic extends Model
{
    use BaseOptionsTrait, ScopeTrait;
    
    public $field = [
        //
        'id',
        //
        'uid',
        // 文章内容
        'content',
        // 图片列表
        'img',
        // 喜欢列表
        'likes',
        // 评论列表
        'comments',
        // 创建时间
        'created_at',
    ];
    
    public $name = 'api_user_dynamic';


    public function getList()
    {
        $data = $this->catchSearch()
            ->catchOrder()
            ->paginate();



        return $data;
    }
}