<?php 

namespace Moonlight\Main;

use Moonlight\Properties\OrderProperty;
use Moonlight\Properties\DatetimeProperty;
use Moonlight\Properties\BaseProperty;

class Item 
{
    const DEFAULT_PER_PAGE = 10;
    
	public $properties = [];

	protected $name = null;
	protected $title = null;
	protected $mainProperty = null;
	protected $root = false;
    protected $create = false;
	protected $orderProperty = null;
	protected $elementPermissions = false;
	protected $perPage = null;
	protected $orderBy = [];

	public function __construct($name) {
		static::assertClass($name);

		$this->name = $name;

		return $this;
	}

	public static function create($name)
	{
		return new self($name);
	}

	public static function assertClass($name)
	{
		$parents = class_parents($name);

		if ( ! isset($parents['Illuminate\Database\Eloquent\Model'])) {
			throw new \Exception("Class $name must extend class Illuminate\Database\Eloquent\Model.");
		}
	}

	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getNameId()
	{
		return str_replace('\\', Element::ID_SEPARATOR, $this->name);
	}

	public function getClass()
	{
		return new $this->name;
	}

	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function setMainProperty($mainProperty)
	{
		$this->mainProperty = $mainProperty;

		return $this;
	}

	public function getMainProperty()
	{
		return $this->mainProperty;
	}

	public function getMainPropertyTitle()
	{
		$mainProperty = $this->getPropertyByName($this->mainProperty);

		return $mainProperty ? $mainProperty->getTitle() : null;
	}

	public function setRoot($value = true)
	{
		$this->root = $value;

		return $this;
	}

	public function getRoot()
	{
		return $this->root;
	}
    
    public function setCreate($value = true)
	{
		$this->create = $value;

		return $this;
	}

	public function getCreate()
	{
		return $this->create;
	}

	public function setOrderProperty($orderProperty)
	{
		$this->orderProperty = $orderProperty;

		return $this;
	}

	public function getOrderProperty()
	{
		return $this->orderProperty;
	}

	public function setElementPermissions($elementPermissions)
	{
		$this->elementPermissions = $elementPermissions;

		return $this;
	}

	public function getElementPermissions()
	{
		return $this->elementPermissions;
	}

	public function setPerPage($perPage)
	{
		$this->perPage = $perPage;

		return $this;
	}

	public function getPerPage()
	{
		return $this->perPage ?: self::DEFAULT_PER_PAGE;
	}

	public function addOrderBy($field, $direction = 'asc')
	{
		$this->orderBy[$field] = $direction;

		return $this;
	}

	public function getOrderByList()
	{
		return $this->orderBy;
	}

	public function addProperty(BaseProperty $property)
	{
		$property->setItem($this);

		$this->properties[$property->getName()] = $property;

		return $this;
	}

	public function addOrder($name = 'order', $direction = 'asc')
	{
		$this->
		addOrderBy($name, $direction)->
		addProperty(
			OrderProperty::create($name)
		);

		return $this;
	}

	public function addTimestamps()
	{
		$this->
		addProperty(
			DatetimeProperty::create('created_at')->
			setTitle('Создано')->
            setReadonly(true)->
			setShow(true)
		)->
		addProperty(
			DatetimeProperty::create('updated_at')->
			setTitle('Изменено')->
            setReadonly(true)
		);

		return $this;
	}

	public function addSoftDeletes()
	{
		$this->
		addProperty(
			DatetimeProperty::create('deleted_at')->
			setTitle('Удалено')->
			setReadonly(true)
		);

		return $this;
	}

	public function getPropertyList()
	{
		return $this->properties;
	}

	public function getPropertyByName($name)
	{
		return
			isset($this->properties[$name])
			? $this->properties[$name]
			: null;
	}
}
