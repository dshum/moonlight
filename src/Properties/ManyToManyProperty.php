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
	protected $showOrder = false;
    
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

	public function setShowOrder($showOrder)
	{
		$this->showOrder = $showOrder;

		return $this;
	}

	public function getShowOrder()
	{
		return $this->showOrder;
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
        $site = \App::make('site');
        
		$name = $this->getName();
        $relatedClass = $this->getRelatedClass();
		$relatedItem = $site->getItemByName($relatedClass);
		$mainProperty = $relatedItem->getMainProperty();
        
		$this->element = $element;

		if (method_exists($this->element, $name)) {
			$this->setList($this->element->{$name}()->get());
		}

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
        $name = $this->getName();
		$ids = $this->buildInput();

		try {
			if (method_exists($this->element, $name)) {
				$this->element->{$name}()->sync($ids);
			}
		} catch (\Exception $e) {}

		return $this;
	}

	public function drop()
	{
		$name = $this->getName();

		try {
			if (method_exists($this->element, $name)) {
				$this->element->{$name}()->detach();
			}
		} catch (\Exception $e) {}

		return $this;
	}

	public function sync($ids)
	{
        $name = $this->getName();

		try {
			if (method_exists($this->element, $name)) {
				$this->element->{$name}()->sync($ids);
			}
		} catch (\Exception $e) {}

		return $this;
	}

	public function attach($id)
	{
        $name = $this->getName();

		try {
			if (method_exists($this->element, $name)) {
				$this->element->{$name}()->attach($id);
			}
		} catch (\Exception $e) {}

		return $this;
	}

	public function detach($id)
	{
        $name = $this->getName();

		try {
			if (method_exists($this->element, $name)) {
				$this->element->{$name}()->detach($id);
			}
		} catch (\Exception $e) {}

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
