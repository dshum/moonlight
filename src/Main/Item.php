<?php

namespace Moonlight\Main;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Moonlight\Properties\OrderProperty;
use Moonlight\Properties\DatetimeProperty;
use Moonlight\Properties\BaseProperty;

/**
 * Class Item
 *
 * @package Moonlight\Main
 */
class Item
{
    /**
     * Default number of elements per page.
     */
    const DEFAULT_PER_PAGE = 10;
    /**
     * @var array
     */
    public $properties = [];
    /**
     * @var
     */
    protected $namespace;
    /**
     * @var string|string[]
     */
    protected $name;
    /**
     * @var
     */
    protected $title;
    /**
     * @var
     */
    protected $className;
    /**
     * @var
     */
    protected $class;
    /**
     * @var
     */
    protected $mainProperty;
    /**
     * @var bool
     */
    protected $root = false;
    /**
     * @var bool
     */
    protected $create = false;
    /**
     * @var
     */
    protected $orderProperty;
    /**
     * @var bool
     */
    protected $elementPermissions = false;
    /**
     * @var
     */
    protected $perPage;
    /**
     * @var array
     */
    protected $orderBy = [];

    /**
     * Item constructor.
     *
     * @param $class
     * @throws \Exception
     */
    public function __construct($class)
    {
        if (! class_exists($class)) {
            throw new Exception("Class $class doesn't exist");
        }

        $this->className = $class;
        $this->class = new $class;

        if (! $this->class instanceof Model) {
            throw new Exception("Class $class must extend class ".Model::class);
        }

        $this->name = str_replace('\\', Site::CLASS_ID_SEPARATOR, $this->className);

        return $this;
    }

    /**
     * @param $class
     * @return \Moonlight\Main\Item
     * @throws \Exception
     */
    public static function create($class)
    {
        return new self($class);
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|string[]
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getClassBaseName()
    {
        return class_basename($this->class);
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $mainProperty
     * @return $this
     */
    public function setMainProperty($mainProperty)
    {
        $this->mainProperty = $mainProperty;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMainProperty()
    {
        return $this->mainProperty;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setRoot(bool $value = true)
    {
        $this->root = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setCreate(bool $value = true)
    {
        $this->create = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function getCreate()
    {
        return $this->create;
    }

    /**
     * @param $orderProperty
     * @return $this
     */
    public function setOrderProperty($orderProperty)
    {
        $this->orderProperty = $orderProperty;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderProperty()
    {
        return $this->orderProperty;
    }

    /**
     * @param $elementPermissions
     * @return $this
     */
    public function setElementPermissions($elementPermissions)
    {
        $this->elementPermissions = $elementPermissions;

        return $this;
    }

    /**
     * @return bool
     */
    public function getElementPermissions()
    {
        return $this->elementPermissions;
    }

    /**
     * @param $perPage
     * @return $this
     */
    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage ?: self::DEFAULT_PER_PAGE;
    }

    /**
     * @param $field
     * @param string $direction
     * @return $this
     */
    public function addOrderBy($field, string $direction = 'asc')
    {
        $this->orderBy[$field] = $direction;

        return $this;
    }

    /**
     * @return array
     */
    public function getOrderByList()
    {
        return $this->orderBy;
    }

    /**
     * @param \Moonlight\Properties\BaseProperty $property
     * @return $this
     */
    public function addProperty(BaseProperty $property)
    {
        $property->setItem($this);

        $this->properties[$property->getName()] = $property;

        return $this;
    }

    /**
     * @param string $name
     * @param string $direction
     * @return $this
     */
    public function addOrder(string $name = 'order', string $direction = 'asc')
    {
        $this
            ->addOrderBy($name, $direction)
            ->addProperty(
                OrderProperty::create($name)
            );

        return $this;
    }

    /**
     * @param bool $readonly
     * @return $this
     */
    public function addTimestamps(bool $readonly = true)
    {
        $this
            ->addProperty(
                DatetimeProperty::create('created_at')->
                setTitle('Создано')->
                setReadonly($readonly)->
                setFillNow(true)->
                setRequired(true)->
                setShow(true)
            )
            ->addProperty(
                DatetimeProperty::create('updated_at')->
                setTitle('Изменено')->
                setReadonly(true)
            );

        return $this;
    }

    /**
     * @return $this
     */
    public function addSoftDeletes()
    {
        $this
            ->addProperty(
                DatetimeProperty::create('deleted_at')->
                setTitle('Удалено')->
                setReadonly(true)
            );

        return $this;
    }

    /**
     * @return array
     */
    public function getPropertyList()
    {
        return $this->properties;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getPropertyByName(string $name)
    {
        return $this->properties[$name] ?? null;
    }

    public function boundToRoot()
    {
        $site = App::make('site');

        $rootBindings = $site->getRootBindings();

        foreach ($rootBindings as $rootBinding) {
            if ($rootBinding == $this->getClassName()) {
                return true;
            }
        }

        return false;
    }

    public function boundToItem(Item $item)
    {
        $site = App::make('site');

        $itemBindings = $site->getItemBindingsByItem($item);

        foreach ($itemBindings as $itemBinding) {
            if ($itemBinding == $this->getClassName()) {
                return true;
            }
        }

        return false;
    }

    public function bindingElements(Item $item)
    {
        $site = App::make('site');

        $elementBindings = $site->getElementBindingsByItem($item);

        $elements = [];

        foreach ($elementBindings as $id => $bindings) {
            foreach ($bindings as $binding) {
                if ($binding == $this->getClassName()) {
                    if ($element = $item->getClass()->find($id)) {
                        $elements[] = $element;
                    }
                }
            }
        }

        return $elements;
    }
}
