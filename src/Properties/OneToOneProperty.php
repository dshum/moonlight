<?php 

namespace Moonlight\Properties;

use Illuminate\Database\Eloquent\Model;
use Moonlight\Main\Site;
use Moonlight\Main\Item;
use Moonlight\Main\Element;

class OneToOneProperty extends BaseProperty 
{
	protected $relatedClass = null;
	protected $parent = false;
	protected $showOrder = false;

	public function __construct($name) {
		parent::__construct($name);

		$this->
		addRule('integer', 'Идентификатор элемента должен быть целым числом');

		return $this;
	}

	public static function create($name)
	{
		return new self($name);
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

	public function setParent($parent = true)
	{
		$this->parent = $parent;

		return $this;
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function setShowOrder($showOrder)
	{
		$this->showOrder = $showOrder;

		return $this;
	}

	public function getShowOrder()
	{
		return $this->showOrder;
	}

	public function setElement(Model $element)
	{
		$this->element = $element;

		$site = \App::make('site');

		$relatedClass = $this->getRelatedClass();
		$relatedItem = $site->getItemByName($relatedClass);
		$mainProperty = $relatedItem->getMainProperty();
		$id = $this->element->{$this->getName()};

		if ($relatedClass && $id) {
			$this->value = $relatedClass::find($id);
		} else {
            $this->value = null;
        }

		return $this;
	}

	public function setRelation(Model $relation)
	{
		if ($this->getRelatedClass() == Element::getClass($relation)) {
			$this->value = $relation;
		}

		return $this;
	}

	public function searchQuery($query)
	{
        $request = $this->getRequest();
		$name = $this->getName();

		$value = (int)$request->input($name);

		if ($value) {
			$query->where($name, $value);
		}

		return $query;
	}

	public function getListView()
	{
		$site = \App::make('site');
		
		$relatedClass = $this->getRelatedClass();
		$relatedItem = $site->getItemByName($relatedClass);
		$mainProperty = $relatedItem->getMainProperty();
		
		$value = $this->value ? [
            'id' => $this->value->id,
            'classId' => Element::getClassId($this->value),
            'name' => $this->value->{$mainProperty},
        ] : null;

		$scope = [
            'name' => $this->getName(),
			'title' => $this->getTitle(),
			'value' => $value,
		];

		return $scope;
	}

	public function getEditView()
	{
		$site = \App::make('site');

		$currentItem = $this->getItem();
		$relatedClass = $this->getRelatedClass();
		$relatedItem = $site->getItemByName($relatedClass);
		$mainProperty = $relatedItem->getMainProperty();
        
        $value = $this->value ? [
            'id' => $this->value->id,
            'classId' => Element::getClassId($this->value),
            'name' => $this->value->{$mainProperty},
		] : null;
		
		$binds = $site->getBinds();
		$itemPlace = null;
		$rootPlace = null;
        $elementPlaces = [];

        foreach ($binds as $parent => $items) {
			foreach ($items as $item) {
				if ($item != $currentItem->getNameId()) continue;

                if ($parent == Site::ROOT && ! $this->getRequired()) {
                    $rootPlace = Site::ROOT;

                    continue;
                }

                $parentItem = $site->getItemByName($parent);

                if ($parentItem && $parentItem->getNameId() == $relatedItem->getNameId()) {
                    $itemPlace = $parentItem->getName();

                    continue;
				}

                $parentElement = Element::getByClassId($parent);

                if ($parentElement) {
					$parentElementItem = Element::getItem($parentElement);
					$parentElementMainProperty = $parentElementItem->getMainProperty();

					if ($parentElementItem->getNameId() == $relatedItem->getNameId()) {
						$elementPlaces[] = [
							'id' => $parentElement->id,
							'classId' => Element::getClassId($parentElement),
							'name' => $parentElement->{$parentElementMainProperty},
						];
					}

                    continue;
                }
            }
		}

		$countPlaces = sizeof($elementPlaces);

		if ($rootPlace) $countPlaces++;

		$scope = [
			'name' => $this->getName(),
			'title' => $this->getTitle(),
			'value' => $value,
			'readonly' => $this->getReadonly(),
			'required' => $this->getRequired(),
			'relatedClass' => $relatedItem->getNameId(),
			'itemPlace' => $itemPlace,
			'rootPlace' => $rootPlace,
			'elementPlaces' => $elementPlaces,
			'countPlaces' => $countPlaces,
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

	public function isOneToOne()
	{
		return true;
	}
}
