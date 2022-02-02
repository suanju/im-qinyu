<?php

namespace catchAdmin\users\controller;

use catchAdmin\permissions\model\Users;
use catcher\base\CatchRequest as Request;
use catcher\CatchResponse;
use catcher\base\CatchController;
use catchAdmin\users\model\User as UserModel;
use catcher\Utils;
use think\Response;

class User extends CatchController
{
    
    protected $userModel;
    
    /**
     *
     * @time 2021/12/30 13:55
     * @param UserModel $userModel
     * @return mixed
     */
    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }
    
    /**
     *
     * @time 2021/12/30 13:55
     * @return Response
     */
    public function index() : Response
    {
        return CatchResponse::paginate($this->userModel->getList());
    }
    
    /**
     *
     * @time 2021/12/30 13:55
     * @param Request $request
     * @return Response
     */
    public function save(Request $request) : Response
    {
        return CatchResponse::success($this->userModel->storeBy($request->post()));
    }
    
    /**
     *
     * @time 2021/12/30 13:55
     * @param $id
     * @return Response
     */
    public function read($id) : Response
    {
        return CatchResponse::success($this->userModel->findBy($id));
    }
    
    /**
     *
     * @time 2021/12/30 13:55
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function update($id, Request $request) : Response
    {
        return CatchResponse::success($this->userModel->updateBy($id, $request->post()));
    }
    
    /**
     *
     * @time 2021/12/30 13:55
     * @param $id
     * @return Response
     */
    public function delete($id) : Response
    {
        return CatchResponse::success($this->userModel->deleteBy($id));
    }

    public function switchStatus($id): \think\response\Json
    {
        $ids = Utils::stringToArrayBy($id);

        foreach ($ids as $_id) {
            $user = $this->userModel->findBy($_id);

            $this->userModel->updateBy($_id, [
                'status' => $user->status == Users::ENABLE ? Users::DISABLE : Users::ENABLE,
            ]);
        }

        return CatchResponse::success([], '操作成功');
    }
}