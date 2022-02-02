<?php
// +----------------------------------------------------------------------
// | CatchAdmin [Just Like ～ ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017~2020 http://catchadmin.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( https://github.com/yanwenwu/catch-admin/blob/master/LICENSE.txt )
// +----------------------------------------------------------------------
// | Author: JaguarJack [ njphper@gmail.com ]
// +----------------------------------------------------------------------
namespace catchAdmin\domain\support\driver\aliyun;

use catchAdmin\domain\support\contract\DomainRecordInterface;
use catchAdmin\domain\support\driver\ApiTrait;
use catchAdmin\domain\support\Transformer;

class DomainRecord implements DomainRecordInterface
{
    use ApiTrait;

    /**
     * 列表
     *
     * @param array $params
     * @return mixed
     */
    public function getList(array $params)
    {
        $data = [
            'Action' => 'DescribeDomainRecords',
            'DomainName' => $params['name'],
            'PageNumber' => $params['page'] ?? 1,
            'PageSize' => $params['limit'] ?? 20,
        ];

        if ($params['rr']) {
            $data['RRKeyWord'] = $params['rr'];
        }

        if ($params['type']) {
            $data['TypeKeyWord'] = $params['type'];
        }

        if ($params['value']) {
            $data['ValueKeyWord'] = $params['value'];
        }

        if ($params['line']) {
            $data['Line'] = $params['line'];
        }

        if ($params['status']) {
            $data['Status'] = $params['status'];
        }

        // TODO: Implement getList() method.
        return Transformer::aliyunDomainRecordPaginate($this->get($data));
    }

    /**
     * 新增解析
     *
     * @param array $params
     * @return array
     *
     */
    public function store(array $params)
    {
        // TODO: Implement add() method.
        return $this->get([
            'Action' => 'AddDomainRecord',
            'DomainName' => $params['name'],
            'RR' => $params['rr'],
            'Type' => $params['type'],
            'Value' => $params['value'],
            'Line' => $params['line'],
            'TTL' => $params['ttl'],
        ]);
    }

    /**
     * 删除解析
     *
     * @param $recordId
     * @return array
     */
    public function delete($recordId)
    {
        // TODO: Implement delete() method.
        return $this->get([
            'Action' => 'DeleteDomainRecord',
            'RecordId' => $recordId
        ]);
    }

    /**
     * 获取解析记录
     *
     * @param array $params
     * @return array
     */
    public function read(array $params)
    {
        // TODO: Implement info() method.
        return $this->get([
            'Action' => 'DescribeDomainRecord',
            'RecordId' => $params['record_id'],
        ]);
    }

    /**
     * 更新解析
     *
     * @param array $params
     * @param $recordId
     * @return array
     */
    public function update($recordId, array $params)
    {
        // TODO: Implement update() method.
        return $this->get([
            'Action' => 'UpdateDomainRecord',
            'RecordId' => $recordId,
            'RR' => $params['rr'],
            'Type' => $params['type'],
            'Value' => $params['value'],
            'Line' => $params['line'],
            'TTL' => $params['ttl'],
        ]);
    }

    /**
     * 设置状态
     *
     * @param $recordId
     * @param $status
     * @return array
     */
    public function enable($recordId, $status)
    {
        return $this->get([
            'Action' => 'SetDomainRecordStatus',
            'RecordId' => $recordId,
            'Status' => ucfirst(strtolower($status))
        ]);
    }
}