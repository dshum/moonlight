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
        $list = $this->getList();
        $ids = [];

        foreach ($list as $element) {
            $ids[] = $element->id;
        }

        return $ids;
    }

    public function setElement(Model $element)
    {
        $name = $this->getName();

        $this->element = $element;
        $this->setList($this->element->{$name}()->get());

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

        $name = $this->getName();
        $ids = $this->buildInput();

        $this->element->{$name}()->sync($ids);

        return $this;
    }

    public function setAfterCreate()
    {
        $name = $this->getName();
        $ids = $this->buildInput();

        if ($this->getOrderField() && is_array($ids)) {
            $array = [];
            foreach ($ids as $id) {
                $array[$id] = [$this->getOrderField() => $this->element->id];
            }
        } else {
            $array = $ids;
        }

        $this->element->{$name}()->sync($array);

        return $this;
    }

    public function drop()
    {
        $name = $this->getName();

        $this->element->{$name}()->detach();

        return $this;
    }

    public function find($id)
    {
        $name = $this->getName();

        return $this->element->{$name}()->find($id);
    }

    public function sync($ids)
    {
        $name = $this->getName();

        $this->element->{$name}()->sync($ids);

        return $this;
    }

    public function attach($id)
    {
        $name = $this->getName();

        $this->element->{$name}()->attach($id);

        return $this;
    }

    public function detach($id)
    {
        $name = $this->getName();

        $this->element->{$name}()->detach($id);

        return $this;
    }

    public function searchQuery($query)
    {
        $site = App::make('site');

        $relatedClass = $this->getRelatedClass();
        $relatedItem = $site->getItemByClassName($relatedClass);
        $relatedMethod = $this->getRelatedMethod();
        $request = $this->getRequest();
        $name = $this->getName();

        $value = (int) $request->input($name);

        if ($value) {
            $bind = $relatedItem->getClass()->find($value);

            if ($bind && method_exists($bind, $relatedMethod)) {
                $elements = $bind->{$relatedMethod}()->withTrashed()->get();
                $ids = [];

                foreach ($elements as $element) {
                    $ids[] = $element->id;
                }

                if (sizeof($ids)) {
                    $query->whereIn('id', $ids);
                }
            }
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
