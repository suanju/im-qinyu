<?php
use app\ExceptionHandle;
use app\Request;

// 容器Provider定义文件
return [
    'think\exception\Handle' => app\common\exception\Http::class,
];
