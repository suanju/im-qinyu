<?php
namespace catchAdmin\group\model\search;


trait GroupSearch
{
    public function searchNameAttr($query, $value, $data)
    {
        return $query->whereLike('name', $value);
    }

}
