<?php
namespace catchAdmin\users\model\search;


trait ChatSearch
{
    public function searchUidAttr($query, $value, $data)
    {
        return $query->whereLike('uid', $value);
    }
    public function searchFidAttr($query, $value, $data)
    {
        return $query->whereLike('fid', $value);
    }
}
