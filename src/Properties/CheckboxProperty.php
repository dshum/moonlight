<?php

namespace Moonlight\Properties;

use Illuminate\Database\Eloquent\Model;

class CheckboxProperty extends BaseProperty
{
    public static function create($name)
    {
        return new self($name);
    }

    public function setElement(Model $element)
    {
        $this->element = $element;
        $this->value = (bool) $element->{$this->getName()};

        return $this;
    }

    public function getEditable()
    {
        return $this->editable;
    }

    public function searchQuery($query)
    {
        $request = $this->getRequest();
        $name = $this->getName();

        $value = $request->input($name);

        if ($value === 'true') {
            $query->where($name, 1);
        } elseif ($value === 'false') {
            $query->where($name, 0);
        }

        return $query;
    }

    public function set()
    {
        $request = $this->getRequest();
        $name = $this->getName();

        $this->element->$name = $request->has($name) && $request->input($name);

        return $this;
    }

    public function getEditableView()
    {
        return [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'value' => $this->getValue(),
            'element' => $this->getElement(),
        ];
    }
}
