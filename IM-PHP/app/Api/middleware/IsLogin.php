<?php

namespace app\api\middleware;


use app\BaseController;
use app\common\model\api\User;

class IsLogin extends BaseController
{

    public function handle($request, \Closure $next) {
        $token = $this -> getToken();
        if (empty($token)){
            return $this -> show(
                config('status.goto'),
                config('message.goto'),
                "非法请求！"
            );
        }
        $user = $this -> getUser();
        if (empty($user)){
            return $this -> show(
                config('status.goto'),
                config('message.goto'),
                "登陆过期！"
            );
        }
        $user = (new User()) -> findByUserNameWithStatus($user['username']);
        if ($user['last_login_token'] != $token){
            return $this -> show(
                config('status.goto'),
                config('message.goto'),
                "账号异地登陆，请重新登陆！"
            );
        }
        return $next($request);
    }

}