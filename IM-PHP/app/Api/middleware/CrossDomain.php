<?php

namespace app\api\middleware;

use think\Response;

/**
 * 跨域设置
 * Class CrossDomain
 * @package app\middleware
 */
class CrossDomain
{
    /**
     * 设置跨域
     * @param $request
     * @param \Closure $next
     *
     * @return mixed|void
     */
    public function handle($request, \Closure $next)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Max-Age: 1800');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With, Token ,groupId');
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        return $next($request);
    }
}
