<?php

namespace catchAdmin\group\controller;

use catcher\base\CatchRequest as Request;
use catcher\CatchResponse;
use catcher\base\CatchController;
use catchAdmin\group\model\Group as GroupModel;
use think\Response;

class Group extends CatchController
{
    
    protected $groupModel;
    
    /**
     *
     * @time 2022/01/03 00:18
     * @param GroupModel $groupModel
     * @return mixed
     */
    public function __construct(GroupModel $groupModel)
    {
        $this->groupModel = $groupModel;
    }
    
    /**
     *
     * @time 2022/01/03 00:18
     * @return Response
     */
    public function index() : Response
    {
        return CatchResponse::paginate($this->groupModel->getList());
    }
    
    /**
     *
     * @time 2022/01/03 00:18
     * @param Request $request
     * @return Response
     */
    public function save(Request $request) : Response
    {
        return CatchResponse::success($this->groupModel->storeBy($request->post()));
    }
    
    /**
     *
     * @time 2022/01/03 00:18
     * @param $id
     * @return Response
     */
    public function read($id) : Response
    {
        return CatchResponse::success($this->groupModel->findBy($id));
    }
    
    /**
     *
     * @time 2022/01/03 00:18
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function update($id, Request $request) : Response
    {
        return CatchResponse::success($this->groupModel->updateBy($id, $request->post()));
    }
    
    /**
     *
     * @time 2022/01/03 00:18
     * @param $id
     * @return Response
     */
    public function delete($id) : Response
    {
        return CatchResponse::success($this->groupModel->deleteBy($id));
    }

    //获取群成员
    public function getGroupMembers($name){
        return CatchResponse::success($this->groupModel->getGroupMembers($name));
    }
}