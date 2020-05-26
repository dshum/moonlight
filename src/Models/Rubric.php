<?php

namespace Moonlight\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Moonlight\Main\Element;

/**
 * Class Rubric
 *
 * @package Moonlight\Models
 */
class Rubric
{
    /**
     * @var null
     */
    protected $name = null;
    /**
     * @var null
     */
    protected $title = null;
    /**
     * @var array
     */
    protected $bindings = [];
    /**
     * @var array
     */
    protected $rootBindings = [];
    /**
     * @var array
     */
    protected $classBindings = [];

    /**
     * Rubric constructor.
     *
     * @param $name
     * @param $title
     */
    public function __construct($name, $title)
    {
        $this->name = $name;
        $this->title = $title;

        return $this;
    }

    /**
     * @param $name
     * @param $title
     * @return \Moonlight\Models\Rubric
     */
    public static function create($name, $title)
    {
        return new self($name, $title);
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
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
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
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $className
     * @param string|null $parent
     * @param callable|null $clause
     * @return $this
     */
    public function bind(string $className, string $parent = null, callable $clause = null)
    {
        $this->bindings[] = (object) [
            'className' => $className,
            'parent' => $parent,
            'clause' => $clause,
        ];

        if (! $parent) {
            $this->rootBindings[] = (object) [
                'className' => $className,
                'clause' => $clause,
            ];
        } else {
            $this->classBindings[$parent][] = (object) [
                'className' => $className,
                'clause' => $clause,
            ];
        }

        return $this;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getBindings()
    {
        return collect($this->bindings);
    }

    public function getRootBindings()
    {
        return collect($this->rootBindings);
    }

    public function getBindingsByClass(string $className)
    {
        $classBindings = $this->classBindings[$className] ?? [];

        return collect($classBindings);
    }

    public function getElements(string $className, Model $parent = null, callable $clause = null)
    {
        $site = App::make('site');

        $item = $site->getItemByClassName($className);

        $query = $this->getElementsQuery($className, $parent, $clause);

        $orderByList = $item->getOrderByList();

        foreach ($orderByList as $field => $direction) {
            $query->orderBy($field, $direction);
        }

        return $query->get();
    }

    public function getElementsCount(string $className, Model $parent = null, callable $clause = null)
    {
        return $this->getElementsQuery($className, $parent, $clause)->count();
    }

    protected function getElementsQuery(string $className, Model $parent = null, callable $clause = null)
    {
        $loggedUser = Auth::guard('moonlight')->user();
        $site = App::make('site');

        $item = $site->getItemByClassName($className);
        $parentClass = $parent ? $site->getClass($parent) : null;

        $propertyList = $item->getPropertyList();
        $criteria = $item->getClass();

        foreach ($propertyList as $property) {
            if (
                $parent
                && $property->isManyToMany()
                && $property->getRelatedClass() == $parentClass
            ) {
                $criteria = $parent->{$property->getRelatedMethod()}();
                break;
            } elseif (
                $parent
                && $property->isOneToOne()
                && $property->getRelatedClass() == $parentClass
            ) {
                $criteria = $item->getClass()->where($property->getName(), $parent->id);
                break;
            }
        }

        return $criteria->where(function ($query) use ($loggedUser, $item) {
            $permissionDenied = true;
            $deniedElementList = [];
            $allowedElementList = [];

            if (! $loggedUser->isSuperUser()) {
                foreach ($loggedUser->groups as $group) {
                    $groupItemPermission = $group->getItemPermission($item);
                    $itemPermission = $groupItemPermission
                        ? $groupItemPermission->permission
                        : $group->default_permission;

                    if ($itemPermission != 'deny') {
                        $permissionDenied = false;
                        $deniedElementList = [];
                    }

                    $elementPermissions = $group->getElementPermissionsByItem($item);

                    foreach ($elementPermissions as $elementPermission) {
                        $element_id = $elementPermission->element_id;
                        $permission = $elementPermission->permission;

                        if ($permission == 'deny') {
                            $deniedElementList[$element_id] = $element_id;
                        } else {
                            $allowedElementList[$element_id] = $element_id;
                        }
                    }
                }

                if ($permissionDenied && sizeof($allowedElementList)) {
                    $query->whereIn('id', $allowedElementList);
                } elseif (! $permissionDenied && sizeof($deniedElementList)) {
                    $query->whereNotIn('id', $deniedElementList);
                }
            }
        })->where($clause);
    }
}
