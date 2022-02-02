<?php

namespace catchAdmin\users\controller;

use catchAdmin\permissions\model\Users;
use catcher\base\CatchRequest as Request;
use catcher\CatchResponse;
use catcher\base\CatchController;
use catchAdmin\users\model\Chat as chatModel;
use catcher\Utils;
use think\Response;

class Chat extends CatchController
{

    protected $chatModel;

    /**
     *
     * @time 2021/12/30 13:55
     * @param chatModel $chatModel
     * @return mixed
     */
    public function __construct(chatModel $chatModel)
    {
        $this->chatModel = $chatModel;
    }

    /**
     *
     * @time 2021/12/30 13:55
     * @return Response
     */
    public function index() : Response
    {
        return CatchResponse::paginate($this->chatModel->getList());
    }

    /**
     *
     * @time 2021/12/30 13:55
     * @param Request $request
     * @return Response
     */
    public function save(Request $request) : Response
    {
        return CatchResponse::success($this->chatModel->storeBy($request->post()));
    }

    /**
     *
     * @time 2021/12/30 13:55
     * @param $id
     * @return Response
     */
    public function read($id) : Response
    {
        return CatchResponse::success($this->chatModel->findBy($id));
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
        return CatchResponse::success($this->chatModel->updateBy($id, $request->post()));
    }

    /**
     *
     * @time 2021/12/30 13:55
     * @param $id
     * @return Response
     */
    public function delete($id) : Response
    {
        return CatchResponse::success($this->chatModel->deleteBy($id));
    }

    public function switchStatus($id): \think\response\Json
    {
        $ids = Utils::stringToArrayBy($id);

        foreach ($ids as $_id) {
            $user = $this->chatModel->findBy($_id);

            $this->chatModel->updateBy($_id, [
                'status' => $user->status == Users::ENABLE ? Users::DISABLE : Users::ENABLE,
            ]);
        }

        return CatchResponse::success([], '操作成功');
    }
}