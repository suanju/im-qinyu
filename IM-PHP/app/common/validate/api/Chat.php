<?php


namespace app\common\validate\api;


use think\Validate;

class Chat extends Validate
{

    protected $rule = [
        'fid' => ['require', 'number'],
        'page|页码' => ['require', 'number'],
        'rows|单页数量' => ['require', 'number']
    ];

    protected $scene = [
        'record' => ['fid','page']
    ];

}