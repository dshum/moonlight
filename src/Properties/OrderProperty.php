<?php

namespace Moonlight\Properties;

class OrderProperty extends BaseProperty
{
    protected $relatedProperty = null;

    public static function create($name)
    {
        return new self($name);
    }

    public function getRelatedProperty()
    {
        return $this->relatedProperty;
    }

    public function setRelatedProperty($relatedProperty)
    {
        $this->relatedProperty = $relatedProperty;

        return $this;
    }

    public function getTitle()
    {
        return 'Порядок';
    }

    public function getReadonly()
    {
        return false;
    }

    public function getHidden()
    {
        return true;
    }

    public function set()
    {
        $name = $this->getName();

        if ($this->element->$name === null) {
            $order = $this->element->max($name);
            $this->element->$name = $order !== null ? $order + 1 : 0;
        }

        return $this;
    }

    public function searchQuery($query)
    {
        return $query;
    }

    public function getEditView()
    {
        return null;
    }
}
