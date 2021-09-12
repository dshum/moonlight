<?php

namespace Moonlight\Properties;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class ManyToManyProperty extends BaseProperty
{
    protected $relatedClass = null;
    protected $relatedMethod = null;
    protected $order = null;
    protected $list = [];
    protected $parent = null;

    public function __construct($name)
    {
        parent::__construct($name);

        return $this;
    }

    public static function create($name)
    {
        return new self($name);
    }

    public function isSortable()
    {
        return false;
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

    public function setRelatedMethod($relatedMethod)
    {
        $this->relatedMethod = $relatedMethod;

        return $this;
    }

    public function getRelatedMethod()
    {
        return $this->relatedMethod;
    }

    public function setOrderField($order)
    {
        $this->order = $order;

        return $this;
    }

    public function getOrderField()
    {
        return $this->order;
    }

    public function setList($list)
    {
        $this->list = $list;

        return $this;
    }

    public function getList()
    {
        return $this->list;
    }

    public function getIds()
    {
        return $this->getList()->pluck('id');
    }

    public function setElement(Model $element)
    {
        $this->element = $element;

        $this->setList($this->element->{$this->getName()});

        return $this;
    }

    public function setRelation(Model $relation)
    {
        $site = App::make('site');

        if ($this->getRelatedClass() == $site->getClass($relation)) {
            $this->setList([$relation]);
        }

        return $this;
    }

    public function set()
    {
        if (! $this->element->id) {
            return $this;
        }

        $ids = $this->buildInput();

        $this->element->{$this->getName()}()->sync($ids);

        return $this;
    }

    public function setAfterCreate()
    {
        $ids = $this->buildInput();

        if ($this->getOrderField() && is_array($ids)) {
            $array = [];
            foreach ($ids as $id) {
                $array[$id] = [$this->getOrderField() => $this->element->id];
            }
        } else {
            $array = $ids;
        }

        $this->element->{$this->getName()}()->sync($array);

        return $this;
    }

    public function drop()
    {
        $this->element->{ $this->getName()}()->detach();

        return $this;
    }

    public function find($id)
    {
        return $this->element->{$this->getName()}()->find($id);
    }

    public function sync($ids)
    {
        $this->element->{$this->getName()}()->sync($ids);

        return $this;
    }

    public function attach($id)
    {
        $this->element->{$this->getName()}()->attach($id);

        return $this;
    }

    public function detach($id)
    {
        $this->element->{$this->getName()}()->detach($id);

        return $this;
    }

    public function with($query)
    {
        if (method_exists($this->element, $this->getName())) {
            $query->with($this->getName());
        }

        return $query;
    }

    public function searchQuery($query)
    {
        $name = $this->getName();

        $value = (int) $this->getRequest()->input($name);

        if ($value) {
            $query->whereHas($name, function($q) use ($name, $value) {
                $q->where("{$name}.id", $value);
            });
        }

        return $query;
    }

    public function getListView()
    {
        $site = App::make('site');

        $relatedClass = $this->getRelatedClass();
        $relatedItem = $site->getItemByClassName($relatedClass);
        $mainProperty = $relatedItem->getMainProperty();
        $list = $this->getList();

        $elements = [];

        foreach ($list as $element) {
            $elements[] = (object) [
                'class_id' => $site->getClassId($element),
                'name' => $element->{$mainProperty},
            ];
        }

        return [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'elements' => $elements,
        ];
    }

    public function getEditView()
    {
        $site = App::make('site');

        $relatedClass = $this->getRelatedClass();
        $relatedItem = $site->getItemByClassName($relatedClass);
        $mainProperty = $relatedItem->getMainProperty();
        $list = $this->getList();

        $elements = [];

        foreach ($list as $element) {
            $elements[] = (object) [
                'id' => $element->id,
                'class_id' => $site->getClassId($element),
                'name' => $element->{$mainProperty},
            ];
        }

        return [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'elements' => $elements,
            'readonly' => $this->getReadonly(),
            'required' => $this->getRequired(),
            'relatedItem' => $relatedItem,
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

    public function isManyToMany()
    {
        return true;
    }
}
