<?php

namespace Moonlight\Main;

use Illuminate\Database\Eloquent\Model;
use Moonlight\Models\Rubric;

/**
 * Class Site
 *
 * @package Moonlight\Main
 */
class Site
{
    /**
     * Browse root identificator
     */
    const ROOT = 'Root';
    /**
     * Element classId separator
     */
    const CLASS_ID_SEPARATOR = '.';
    /**
     * @var
     */
    protected $namespace;
    /**
     * @var array
     */
    protected $items = [];
    /**
     * @var array
     */
    protected $classMap = [];
    /**
     * @var array
     */
    protected $rootBonds = [];
    /**
     * @var array
     */
    protected $itemBonds = [];
    /**
     * @var array
     */
    protected $elementBonds = [];
    /**
     * @var array
     */
    protected $rubrics = [];
    /**
     * @var null
     */
    protected $homeComponent = null;
    /**
     * @var array
     */
    protected $browseComponents = [];
    /**
     * @var array
     */
    protected $browseElementComponents = [];
    /**
     * @var array
     */
    protected $itemComponents = [];
    /**
     * @var array
     */
    protected $editComponents = [];

    /**
     * @param string $component
     * @return $this
     */
    public function setHomeComponent(string $component)
    {
        $this->homeComponent = $component;

        return $this;
    }

    /**
     * @return null
     */
    public function getHomeComponent()
    {
        return $this->homeComponent;
    }

    /**
     * @param string $class
     * @param string $component
     * @return $this
     */
    public function addBrowseComponent(string $class, string $component)
    {
        $this->browseComponents[$class] = $component;

        return $this;
    }

    /**
     * @param array $components
     * @return $this
     */
    public function setBrowseComponents(array $components)
    {
        foreach ($components as $class => $component) {
            $this->browseComponents[$class] = $component;
        }

        return $this;
    }

    /**
     * @param string $className
     * @param int $id
     * @param string $component
     * @return $this
     */
    public function addBrowseElementComponent(string $className, int $id, string $component)
    {
        $this->browseElementComponents[$className][$id] = $component;

        return $this;
    }

    /**
     * @param array $components
     * @return $this
     */
    public function setBrowseElementComponents(array $components)
    {
        foreach ($components as $class => $component_group) {
            foreach ($component_group as $id => $component) {
                $this->browseElementComponents[$class][$id] = $component;
            }
        }

        return $this;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $element
     * @return mixed|null
     */
    public function getBrowseComponent(Model $element)
    {
        $className = $this->getClass($element);

        return $this->browseElementComponents[$className][$element->id]
            ?? $this->browseComponents[$className] ?? null;
    }

    /**
     * @param string $className
     * @param string $component
     * @return $this
     */
    public function addItemComponent(string $className, string $component)
    {
        $this->itemComponents[$className] = $component;

        return $this;
    }

    /**
     * @param array $components
     * @return $this
     */
    public function setItemComponents(array $components)
    {
        foreach ($components as $className => $component) {
            $this->itemComponents[$className] = $component;
        }

        return $this;
    }

    /**
     * @param \Moonlight\Main\Item $item
     * @return mixed|null
     */
    public function getItemComponent(Item $item)
    {
        return $this->itemComponents[$item->getClassName()] ?? null;
    }

    /**
     * @param \Moonlight\Models\Rubric $rubric
     * @return $this
     */
    public function addRubric(Rubric $rubric)
    {
        $name = $rubric->getName();

        $this->rubrics[$name] = $rubric;

        return $this;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getRubricList()
    {
        return collect($this->rubrics);
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getRubricByName(string $name)
    {
        return $this->rubrics[$name] ?? null;
    }

    /**
     * @param \Moonlight\Main\Item $item
     * @return $this
     */
    public function addItem(Item $item)
    {
        $this->items[$item->getName()] = $item;
        $this->classMap[$item->getClassName()] = $item->getName();

        return $this;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getItemList()
    {
        return collect($this->items);
    }

    /**
     * @param string|null $itemName
     * @return \Moonlight\Main\Item|null
     */
    public function getItemByName(string $itemName = null): ?Item
    {
        return $this->items[$itemName] ?? null;
    }

    /**
     * @param string|null $className
     * @return \Moonlight\Main\Item|null
     */
    public function getItemByClassName(string $className = null): ?Item
    {
        $name = $this->classMap[$className] ?? null;

        return $this->items[$name] ?? null;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $element
     * @return \Moonlight\Main\Item|null
     */
    public function getItemByElement(Model $element): ?Item
    {
        $class = $this->getClass($element);

        return $this->getItemByClassName($class);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $element
     * @return string
     */
    public function getClass(Model $element)
    {
        return get_class($element);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $element
     * @return string
     */
    public function getClassId(Model $element)
    {
        return
            str_replace('\\', static::CLASS_ID_SEPARATOR, $this->getClass($element))
            .static::CLASS_ID_SEPARATOR
            .$element->getAttribute('id');
    }

    public function getByClassId($classId)
    {
        if (strpos($classId, static::CLASS_ID_SEPARATOR)) {
            $array = explode(static::CLASS_ID_SEPARATOR, $classId);
            $id = (int) array_pop($array);
            $itemName = (string) implode('.', $array);

            $item = $this->getItemByName($itemName);

            if ($item) {
                return $item->getClass()->find($id);
            };
        }

        return null;
    }

    public function getByClassIdWithTrashed($classId)
    {
        if (strpos($classId, static::CLASS_ID_SEPARATOR)) {
            $array = explode(static::CLASS_ID_SEPARATOR, $classId);
            $id = (int) array_pop($array);
            $itemName = (string) implode('.', $array);

            $item = $this->getItemByName($itemName);

            if ($item) {
                return $item->getClass()->withTrashed()->find($id);
            };
        }

        return null;
    }

    public function getByClassIdOnlyTrashed(string $classId)
    {
        if (strpos($classId, static::CLASS_ID_SEPARATOR)) {
            $array = explode(static::CLASS_ID_SEPARATOR, $classId);
            $id = (int) array_pop($array);
            $itemName = (string) implode('.', $array);

            $item = $this->getItemByName($itemName);

            if ($item) {
                return $item->getClass()->onlyTrashed()->find($id);
            };
        }

        return null;
    }

    public function getProperty(Model $element, string $propertyName)
    {
        $item = $this->getItemByElement($element);
        $property = $item->getPropertyByName($propertyName);

        return $property->setElement($element);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $element
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\BelongsTo|object|null
     */
    public function getParent(Model $element)
    {
        $item = $this->getItemByElement($element);
        $propertyList = $item->getPropertyList();

        foreach ($propertyList as $propertyName => $property) {
            if (
                $property->isOneToOne()
                && $property->getRelatedClass()
                && $property->getParent()
                && $element->$propertyName
            ) {
                return $element->belongsTo($property->getRelatedClass(), $propertyName)->first();
            }
        }

        return null;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $element
     * @return array
     */
    public function getParentList(Model $element)
    {
        $parents = [];
        $exists = [];
        $max = 100;
        $count = 0;

        $parent = $this->getParent($element);

        while ($count < $max && $parent instanceof Model) {
            $classId = $this->getClassId($parent);

            if (isset($exists[$classId])) {
                break;
            }

            $parents[] = $parent;
            $exists[$classId] = true;
            $parent = $this->getParent($parent);
            $count++;
        }

        krsort($parents);

        return $parents;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $element
     * @param \Illuminate\Database\Eloquent\Model $parent
     */
    public function setParent(Model $element, Model $parent)
    {
        $item = $this->getItemByElement($element);
        $propertyList = $item->getPropertyList();

        foreach ($propertyList as $propertyName => $property) {
            if (
                $property->isOneToOne()
                && $property->getParent()
                && $property->getRelatedClass() == static::getClass($parent)
            ) {
                $element->$propertyName = $parent->id;
            }
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $element
     * @return string
     */
    public function getBrowseUrl(Model $element)
    {
        return route('moonlight.browse.element', $this->getClassId($element));
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $element
     * @return string
     */
    public function getEditUrl(Model $element)
    {
        return route('moonlight.element.edit', $this->getClassId($element));
    }

    /**
     * @param $bonds
     * @return $this
     */
    public function bindToRoot($bonds)
    {
        if (is_array($bonds)) {
            foreach ($bonds as $bond) {
                $this->rootBonds[$bond] = $bond;
            }
        } elseif (is_string($bonds)) {
            $this->rootBonds[$bonds] = $bonds;
        }

        return $this;
    }

    /**
     * @param string $class
     * @param $bonds
     * @return $this
     */
    public function bindToItem(string $class, $bonds)
    {
        if (is_array($bonds)) {
            foreach ($bonds as $bond) {
                $this->itemBonds[$class][$bond] = $bond;
            }
        } elseif (is_string($bonds)) {
            $this->itemBonds[$class][$bonds] = $bonds;
        }

        return $this;
    }

    /**
     * @param string $class
     * @param int $id
     * @param $bindings
     * @return $this
     */
    public function bindToElement(string $class, int $id, $bindings)
    {
        if (is_array($bindings)) {
            foreach ($bindings as $binding) {
                $this->elementBonds[$class][$id][$binding] = $binding;
            }
        } elseif (is_string($bindings)) {
            $this->elementBonds[$class][$id][$bindings] = $bindings;
        }

        return $this;
    }

    /**
     * @param string $class
     * @param array $bindings
     * @return $this
     */
    public function bindToElements(string $class, array $bindings)
    {
        foreach ($bindings as $id => $binding) {
            if (is_array($binding)) {
                foreach ($binding as $b) {
                    $this->elementBonds[$class][$id][$b] = $b;
                }
            } else {
                $this->elementBonds[$class][$id][$binding] = $binding;
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getRootBindings()
    {
        return $this->rootBonds;
    }

    /**
     * @param \Moonlight\Main\Item $item
     * @return array|mixed
     */
    public function getItemBindingsByItem(Item $item)
    {
        $class = $item->getClassName();

        return $this->itemBonds[$class] ?? [];
    }

    /**
     * @param \Moonlight\Main\Item $item
     * @return array|mixed
     */
    public function getElementBindingsByItem(Item $item)
    {
        $class = $item->getClassName();

        return $this->elementBonds[$class] ?? [];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $element
     * @return array|mixed
     */
    public function getBindings(Model $element)
    {
        $class = $this->getClass($element);
        $id = $element->id;

        return $this->elementBonds[$class][$id] ?? $this->itemBonds[$class] ?? [];
    }

    /**
     * @return null
     */
    public function end()
    {
        return null;
    }
}
