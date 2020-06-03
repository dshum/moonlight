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
        $item = $this->getItem();
        $name = $this->getName();
        $relatedPropertyName = $this->getRelatedProperty();

        $relatedProperty = $relatedPropertyName
            ? $item->getPropertyByName($relatedPropertyName) : null;

        if ($relatedProperty && $relatedProperty->isManyToMany()) {
            return $this;
        }

        if ($this->element->$name === null) {
            $order = $this->element->max($name);
            $this->element->$name = $order === null ? 0 : $order + 1;
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

    public function isOrder()
    {
        return true;
    }
}
