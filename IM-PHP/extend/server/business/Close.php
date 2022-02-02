<?php

namespace server\business;

use server\base\Base;

class Close extends Base
{
    public function close($ws, $fd)
    {
        $uid = $this->getBindUid($ws, $fd);
        if (!empty($uid)) {
            $data = $this->getSocket($uid);
            foreach ($data['fd'] as $key => $value) {
                if ($fd == $value) {
                    unset($data['fd'][$key]);
                }
            }
            $this->redis->set(config('redis.socket_pre') . $uid, $data);
        }
    }
}