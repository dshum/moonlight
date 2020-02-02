<?php

namespace Moonlight\Properties;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Moonlight\Main\Item;
use Moonlight\Main\Element;

class ManyToManyProperty extends BaseProperty
{
	protected $relatedClass = null;
    protected $relatedMethod = null;
    protected $order = null;

	protected $list = [];
	protected $parent = null;

	public function __construct($name) {
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
		Item::assertClass($relatedClass);

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
		if ($this->getRelatedClass() == Element::getClass($relation)) {
			$this->setList([$relation]);
		}

		return $this;
	}

    public function set()
    {
        if (
            $this->getHidden()
            || $this->getReadonly()
            || ! $this->element->id
        ) {
            return $this;
        }

        $name = $this->getName();
        $ids = $this->buildInput();

        $this->element->{$name}()->sync($ids);

        return $this;
    }

    public function setAfterCreate()
	{
        if ($this->getHidden() || $this->getReadonly()) {
            return $this;
        }

        $name = $this->getName();
        $ids = $this->buildInput();

        if ($this->getOrderField()) {
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

	public function sync($ids)
	{
        $name = $this->getName();

        $this->element->{$name}()->sync($ids);

		return $this;
	}

	public function attach($id)
	{
        $name = $this->getName();

        $this->element->{$name}()->syncWithoutDetaching($id);

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
		$site = \App::make('site');

		$relatedClass = $this->getRelatedClass();
		$relatedItem = $site->getItemByName($relatedClass);
		$relatedMethod = $this->getRelatedMethod();
        $request = $this->getRequest();
		$name = $this->getName();

		$value = (int)$request->input($name);

		if ($value) {
			$bind = $relatedItem->getClass()->find($value);

			if ($bind && method_exists($bind, $relatedMethod)) {
				$elements = $bind->{$relatedMethod}()->withTrashed()->get();

				$ids = [];

				foreach ($elements as $element) {
					$ids[] = $element->id;
				}

				if ($ids) {
					$query->whereIn('id', $ids);
				}
			}
		}

		return $query;
	}

    public function getListView()
	{
		$site = \App::make('site');

		$relatedClass = $this->getRelatedClass();
		$relatedItem = $site->getItemByName($relatedClass);
		$mainProperty = $relatedItem->getMainProperty();
		$list = $this->getList();

		$elements = [];

		foreach ($list as $element) {
            $elements[] = [
                'id' => $element->id,
                'classId' => Element::getClassId($element),
                'name' => $element->{$mainProperty},
            ];
        }

		$scope = [
            'name' => $this->getName(),
			'title' => $this->getTitle(),
			'elements' => $elements,
		];

		return $scope;
	}

    public function getEditView()
	{
		$site = \App::make('site');

		$relatedClass = $this->getRelatedClass();
		$relatedItem = $site->getItemByName($relatedClass);
		$mainProperty = $relatedItem->getMainProperty();
		$list = $this->getList();

		$elements = [];

		foreach ($list as $element) {
            $elements[] = [
                'id' => $element->id,
                'classId' => Element::getClassId($element),
                'name' => $element->{$mainProperty},
            ];
        }

		$scope = [
			'name' => $this->getName(),
			'title' => $this->getTitle(),
			'elements' => $elements,
			'readonly' => $this->getReadonly(),
			'required' => $this->getRequired(),
			'relatedClass' => $relatedItem->getNameId(),
			'relatedItem' => $relatedItem,
		];

		return $scope;
	}

    public function getSearchView()
	{
        $site = \App::make('site');

		$request = $this->getRequest();
        $name = $this->getName();
        $id = (int)$request->input($name);
        $relatedClass = $this->getRelatedClass();
		$relatedItem = $site->getItemByName($relatedClass);
        $mainProperty = $relatedItem->getMainProperty();

		$element = $id
            ? $relatedClass::find($id)
            : null;

        $value = $element
            ? [
                'id' => $element->id,
                'name' => $element->{$mainProperty}
            ] : null;

		$scope = array(
			'name' => $this->getName(),
			'title' => $this->getTitle(),
			'value' => $value,
			'open' => $element !== null,
            'relatedClass' => $relatedItem->getNameId(),
		);

		return $scope;
	}

    public function isManyToMany()
	{
		return true;
	}
}
