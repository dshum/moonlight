<?php

namespace Moonlight\Properties;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Moonlight\Main\Site;

class OneToOneProperty extends BaseProperty
{
    protected $relationName = null;
    protected $relatedClass = null;
    protected $parent = false;

    public function __construct($name)
    {
        parent::__construct($name);

        $this->addRule('integer', 'Идентификатор элемента должен быть целым числом');

        return $this;
    }

    public static function create($name)
    {
        return new self($name);
    }

    public function setRelationName($relationName)
    {
        $this->relationName = $relationName;

        return $this;
    }

    public function getRelationName()
    {
        return $this->relationName ?: str_replace('_id', '', $this->name);
    }

    public function setRelatedClass($relatedClass)
    {
        $this->relatedClass = $relatedClass;

        return $this;
    }

    public function getRelatedClass()
    {
        return $this->relatedClass;
    }

    public function setParent($parent = true)
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setElement(Model $element)
    {
        $this->element = $element;

        if (method_exists($element, $this->getRelationName())) {
            $this->value = $element->{$this->getRelationName()};
        } else {
            $relatedClass = $this->getRelatedClass();
            $id = $element->{$this->getName()};
            $this->value = $relatedClass && $id ? $relatedClass::find($id) : null;
        }

        return $this;
    }

    public function setRelation(Model $relation)
    {
        $site = App::make('site');

        if ($this->getRelatedClass() == $site->getClass($relation)) {
            $this->value = $relation;
        }

        return $this;
    }

    public function with($query)
    {
        $instance = $this->getItemClass();

        if (method_exists($instance, $this->getRelationName())) {
            $query->with($this->getRelationName());
        }

        return $query;
    }

    public function searchQuery($query)
    {
        $request = $this->getRequest();
        $name = $this->getName();

        $value = (int) $request->input($name);

        if ($value) {
            $query->where($name, $value);
        }

        return $query;
    }

    public function getListView()
    {
        $site = App::make('site');

        $relatedClass = $this->getRelatedClass();
        $relatedItem = $site->getItemByClassName($relatedClass);
        $mainProperty = $relatedItem->getMainProperty();

        $element = $this->value ? (object) [
            'class_id' => $site->getClassId($this->value),
            'name' => $this->value->{$mainProperty},
        ] : null;

        return [
            'name' => $this->getName(),
            'element' => $element,
        ];
    }

    public function getEditView()
    {
        $site = App::make('site');

        $currentItem = $this->getItem();
        $relatedClass = $this->getRelatedClass();
        $relatedItem = $site->getItemByClassName($relatedClass);
        $mainProperty = $relatedItem->getMainProperty();

        $value = $this->value ? (object) [
            'id' => $this->value->id,
            'class_id' => $site->getClassId($this->value),
            'name' => $this->value->{$mainProperty},
        ] : null;

        $rootPlace = null;
        $itemPlace = null;
        $elementPlaces = [];

        if ($currentItem->boundToRoot() && ! $this->getRequired()) {
            $rootPlace = Site::ROOT;
        }

        if ($currentItem->boundToItem($relatedItem)) {
            $itemPlace = $relatedItem->getName();
        }

        $bindingElements = $currentItem->bindingElements($relatedItem);

        foreach ($bindingElements as $bindingElement) {
            $elementPlaces[] = (object) [
                'id' => $bindingElement->id,
                'class_id' => $site->getClassId($bindingElement),
                'name' => $bindingElement->{$mainProperty},
            ];
        }

        $countPlaces = sizeof($elementPlaces);

        if ($rootPlace) {
            $countPlaces++;
        }

        return [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'value' => $value,
            'readonly' => $this->getReadonly(),
            'required' => $this->getRequired(),
            'relatedItem' => $relatedItem,
            'itemPlace' => $itemPlace,
            'rootPlace' => $rootPlace,
            'elementPlaces' => $elementPlaces,
            'countPlaces' => $countPlaces,
        ];
    }

    public function getSearchView()
    {
        $site = App::make('site');

        $request = $this->getRequest();
        $name = $this->getName();
        $relatedClass = $this->getRelatedClass();
        $relatedItem = $site->getItemByClassName($relatedClass);
        $mainProperty = $relatedItem->getMainProperty();

        $id = (int) $request->input($name);
        $element = $id ? $relatedClass::find($id) : null;

        $value = $element ? (object) [
            'id' => $element->id,
            'class_id' => $site->getClassId($element),
            'name' => $element->{$mainProperty},
        ] : null;

        return [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'value' => $value,
            'open' => $element !== null,
            'relatedItem' => $relatedItem,
        ];
    }

    public function isOneToOne()
    {
        return true;
    }
}
