<?php

namespace Moonlight\Properties;

class VirtualProperty extends BaseProperty
{
    public static function create($name)
    {
        return new self($name);
    }

    public function isSortable()
    {
        return false;
    }

    public function set()
    {
        return $this;
    }

    public function searchQuery($query)
    {
        return $query;
    }

    public function getSearchView()
    {
        return null;
    }

    public function isVirtual()
    {
        return true;
    }
}
