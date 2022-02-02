<?php
namespace catchAdmin\group\tables;


use catcher\CatchTable;
use catchAdmin\group\tables\forms\Factory;
use catcher\library\table\Actions;
use catcher\library\table\HeaderItem;
use catcher\library\table\Search;

class Group extends CatchTable
{
 public function table()
    {
        // TODO: Implement table() method.
        return $this->getTable('api_group')
                    ->header([
                        HeaderItem::label('')->selection(),
                        HeaderItem::label('群聊名')->prop('name'),
                        HeaderItem::label('头像')->prop('portrait')->withPreviewComponent(),
                        HeaderItem::label('群主id')->prop('rootId'),
                        HeaderItem::label('群主昵称')->prop('rootName'),
                        HeaderItem::label('人数')->prop('idsNum'),
                        HeaderItem::label('创建时间')->prop('created_at'),
                        HeaderItem::label('操作')->width(200)->actions([
                            Actions::view(),Actions::delete()
                        ])
                    ])
                    ->withSearch([
                        Search::label('群聊名')->text('name', '群聊名'),
                    ])
                    ->withApiRoute('group')
                    ->selectionChange()
                    ->render();
    }

    protected function form()
    {
        // TODO: Implement form() method.
        return Factory::create('group');
    }

}