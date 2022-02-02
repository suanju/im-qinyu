<?php
// 全局中间件定义文件
return array(
    // 全局请求缓存
    // \think\middleware\CheckRequestCache::class,
    // 多语言加载
    // \think\middleware\LoadLangPack::class,
    // Session初始化
    // \think\middleware\SessionInit::class
    //跨越请求
    \app\Api\middleware\CrossDomain::class

);
