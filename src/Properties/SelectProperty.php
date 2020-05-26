<?php

namespace Moonlight\Properties;

class SelectProperty extends BaseProperty
{
    protected $list = [];

    public static function create($name)
    {
        return new self($name);
    }

    public function getEditable()
    {
        return $this->editable;
    }

    public function setList(array $list)
    {
        $this->list = $list;

        $keys = implode(',', array_keys($this->list));

        $this->addRule("in:$keys", 'Выберите значение из списка');

        return $this;
    }

    public function getList()
    {
        return $this->list;
    }

    public function searchQuery($query)
    {
        $request = $this->getRequest();
        $name = $this->getName();

        $value = $request->input($name);

        if (mb_strlen($value)) {
            $query->where($name, $value);
        }

        return $query;
    }

    public function getListView()
    {
        return [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'value' => $this->getValue(),
            'list' => $this->getList(),
        ];
    }

    public function getEditView()
    {
        return [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'value' => $this->getValue(),
            'list' => $this->getList(),
            'readonly' => $this->getReadonly(),
        ];
    }

    public function getEditableView()
    {
        return [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'value' => $this->getValue(),
            'list' => $this->getList(),
            'element' => $this->getElement(),
        ];
    }

    public function getSearchView()
    {
        $request = $this->getRequest();
        $name = $this->getName();
        $value = $request->input($name);

        if (! mb_strlen($value)) {
            $value = null;
        }

        return [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'value' => $value,
            'list' => $this->getList(),
        ];
    }
}
