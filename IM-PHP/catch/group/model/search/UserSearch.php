<?php
namespace catchAdmin\users\model\search;


trait UserSearch
{
    public function searchUsernameAttr($query, $value, $data)
    {
        return $query->whereLike('username', $value);
    }

    public function searchEmailAttr($query, $value, $data)
    {
        return $query->whereLike('email', $value);
    }

    public function searchStatusAttr($query, $value, $data)
    {
        return $query->where('status', $value);
    }
}
