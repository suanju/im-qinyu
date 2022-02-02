<?php

namespace app\common\exception;


use think\exception\Handle;
use think\Response;

class Http extends Handle
{

    private $msg = null;
    private $status = null;

    public function render($request, \Throwable $e): Response{
        $this -> msg = $e -> getMessage();
        $this -> status = config('status.error');
        if ($this -> msg == config('status.goto')){
            $this -> status = config('status.goto');
        }
        return json([
            'status' => $this -> status,
            'message' => $this -> msg,
            'data' => null,
        ]);
    }

}