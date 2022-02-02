<?php

namespace app\common\business\api;

use app\common\model\api\Chat as ChatModel;
use app\common\model\api\ChatGroup as ChatGroupMode;

class Chat
{

    private $chatModel = NULL;
    private $chatGroupModel = NULL;


    public function __construct() {
        $this -> chatModel = new ChatModel();
        $this -> chatGroupModel = new ChatGroupMode();
    }

    public function record($data){
        $myRecord = $this -> chatModel -> getRecord($data['uid'], $data['fid'],$data['page'],$data['rows'])-> toArray();
        return $myRecord;
        
    }

    public function recordGroup($data){
        $myRecord = $this -> chatGroupModel -> getRecord($data['fid'],$data['page'],$data['rows'])-> toArray();
        return $myRecord;

    }



}