<?php

namespace catchAdmin\dynamic\controller;

use catcher\base\CatchRequest as Request;
use catcher\CatchResponse;
use catcher\base\CatchController;
use catchAdmin\dynamic\model\Dynamic as DynamicModel;
use think\Response;

class Dynamic extends CatchController
{
    
    protected $dynamicModel;
    
    /**
     *
     * @time 2022/01/04 00:33
     * @param DynamicModel $dynamicModel
     * @return mixed
     */
    public function __construct(DynamicModel $dynamicModel)
    {
        $this->dynamicModel = $dynamicModel;
    }
    
    /**
     *
     * @time 2022/01/04 00:33
     * @return Response
     */
    public function index() : Response
    {
        return CatchResponse::paginate($this->dynamicModel->getList());
    }
    
    /**
     *
     * @time 2022/01/04 00:33
     * @param Request $request
     * @return Response
     */
    public function save(Request $request) : Response
    {
        return CatchResponse::success($this->dynamicModel->storeBy($request->post()));
    }
    
    /**
     *
     * @time 2022/01/04 00:33
     * @param $id
     * @return Response
     */
    public function read($id) : Response
    {
        return CatchResponse::success($this->dynamicModel->findBy($id));
    }
    
    /**
     *
     * @time 2022/01/04 00:33
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function update($id, Request $request) : Response
    {
        return CatchResponse::success($this->dynamicModel->updateBy($id, $request->post()));
    }
    
    /**
     *
     * @time 2022/01/04 00:33
     * @param $id
     * @return Response
     */
    public function delete($id) : Response
    {
        return CatchResponse::success($this->dynamicModel->deleteBy($id));
    }
}