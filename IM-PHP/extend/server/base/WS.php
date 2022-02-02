<?php

namespace server\base;
require __DIR__ . '/../../../public/index.php';

use Swoole\WebSocket\Server;
use server\business\Chat as chatBusiness;
use server\business\Close as closeBusiness;

class WS extends Base
{

    private $ws = NULL;
    private $chatBusiness = NULL;
    private $closeBusiness = NULL;

    public function __construct() {
        parent ::__construct();
        $this -> chatBusiness = new chatBusiness();
        $this ->closeBusiness = new closeBusiness();
        $this -> ws = new Server("0.0.0.0", 9501);
        $this -> ws -> set([
            'task_worker_num' => 4,
        ]);
        $this -> ws -> on('open', [$this, "onOpen"]);
        $this -> ws -> on('message', [$this, "onMessage"]);
        $this -> ws -> on('task', [$this, "onTask"]);
        $this -> ws -> on('finish', [$this, "onFinish"]);
        $this -> ws -> on('close', [$this, "onClose"]);
        $this -> ws -> start();
    }
    public function onOpen($ws, $request) {
        $this -> handle($request -> get['token'], $request -> get['type'], $ws, $request -> fd);
    }

    public function onMessage($ws, $frame) {
        var_dump($frame->data);
        $this -> chatBusiness -> switchboard($ws, $frame);
    }

    public function onTask($ws, $task_id, $src_worker_id, $data) {
        return $data;
    }

    public function onFinish($ws, $task_id, $data) {
    }

    public function onClose($ws, $fd) {
        $this -> closeBusiness -> close($ws, $fd);

    }


}
new WS();