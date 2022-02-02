<?php
namespace catchAdmin\dynamic\tables;


use catcher\CatchTable;
use catchAdmin\dynamic\tables\forms\Factory;
use catcher\library\table\Actions;
use catcher\library\table\HeaderItem;
use catcher\library\table\Search;

class Dynamic extends CatchTable
{
 public function table()
    {
        // TODO: Implement table() method.
        return $this->getTable('dynamic')
                    ->header([
                        HeaderItem::label('')->selection(),
                        HeaderItem::label('动态id')->prop('id'),
                        HeaderItem::label('用户id')->prop('uid'),
                        HeaderItem::label('动态内容')->prop('content'),
                        HeaderItem::label('图片列表')->prop('img'),
                        HeaderItem::label('喜欢列表')->prop('likes'),
                        HeaderItem::label('评论列表')->prop('comments'),
                        HeaderItem::label('发布时间')->prop('created_at'),
                        HeaderItem::label('操作')->width(200)->actions([
                            Actions::view(),Actions::delete()
                        ])
                    ])
                    ->withSearch([
                        Search::label('用户名')->text('username', '用户名'),
                    ])
                    ->withApiRoute('dynamic')
                    ->selectionChange()
                    ->render();
    }

    protected function form()
    {
        // TODO: Implement form() method.
        return Factory::create('dynamic');
    }

}