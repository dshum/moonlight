<?php

namespace Moonlight\Properties;

use Moonlight\Main\Element;

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
        $scope = [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'value' => $this->getValue(),
            'list' => $this->getList(),
        ];

        return $scope;
    }

    public function getEditView()
    {
        $scope = [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'value' => $this->getValue(),
            'list' => $this->getList(),
            'readonly' => $this->getReadonly(),
        ];

        return $scope;
    }

    public function getEditableView()
    {
        $scope = array(
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'value' => $this->getValue(),
            'list' => $this->getList(),
            'element' => $this->getElement(),
        );

        return $scope;
    }

    public function getSearchView()
    {
        $request = $this->getRequest();
        $name = $this->getName();
        $value = $request->input($name);

        if (! mb_strlen($value)) {
            $value = null;
        }

        $scope = [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'value' => $value,
            'list' => $this->getList(),
        ];

        return $scope;
    }
}
