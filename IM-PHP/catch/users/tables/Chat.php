<?php
namespace catchAdmin\users\tables;


use catcher\CatchTable;
use catchAdmin\users\tables\forms\Factory;
use catcher\library\table\Actions;
use catcher\library\table\HeaderItem;
use catcher\library\table\Search;

class  Chat extends CatchTable
{
    public function table()
    {
        // TODO: Implement table() method.
        return $this->getTable('api_chat')
            ->header([
                HeaderItem::label('')->selection(),
                HeaderItem::label('消息id')->prop('id'),
                HeaderItem::label('发送者')->prop('uid'),
                HeaderItem::label('接收者')->prop('fid'),
                HeaderItem::label('消息内容')->prop('message'),
                HeaderItem::label('消息类型')->prop('messageType'),
                HeaderItem::label('发送时间')->prop('created_at'),
                HeaderItem::label('操作')->width(200)->actions([Actions::delete()
                ])
            ])
            ->withSearch([
                Search::label('发送者')->text('uid', '发送者'),
                Search::label('接收者')->text('fid', '接收者'),
            ])
            ->withApiRoute('api_chat')
            ->withActions([
                Actions::create(),
                Actions::export()
            ])
            ->selectionChange()
            ->render();
    }

    protected function form()
    {
        // TODO: Implement form() method.
        return Factory::create('user');
    }

}