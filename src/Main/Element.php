<?php

namespace Moonlight\Main;

use App;
use Illuminate\Database\Eloquent\Model;
use Moonlight\Properties\FileProperty;
use Moonlight\Properties\ImageProperty;
use Moonlight\Properties\OrderProperty;

final class Element
{
	const ID_SEPARATOR = '.';

	public static function getItem(Model $element): Item
	{
		$site = App::make('site');

		$class = static::getClass($element);

		return $site->getItemByClassName($class);
	}

	public static function getClass(Model $element)
	{
		return get_class($element);
	}

	public static function getClassId(Model $element)
	{
		return
			str_replace(
				'\\',
				static::ID_SEPARATOR,
				self::getClass($element)
			)
			.static::ID_SEPARATOR
			.$element->id;
	}

	public static function getByClassId($classId)
	{
		if (! strpos($classId, static::ID_SEPARATOR)) return null;

		try {
			$array = explode(static::ID_SEPARATOR, $classId);
			$id = array_pop($array);
			$class = implode('\\', $array);

            $site = App::make('site');

            $item = $site->getItemByName($class);

			if ($item) {
				return $item->getClass()->find($id);
			};
		} catch (\Exception $e) {}

		return null;
	}

	public static function getByClassIdWithTrashed($classId)
	{
		if (! strpos($classId, static::ID_SEPARATOR)) return null;

		try {
			$array = explode(static::ID_SEPARATOR, $classId);
			$id = array_pop($array);
			$class = implode('\\', $array);

			$site = App::make('site');

            $item = $site->getItemByName($class);

			if ($item) {
				return $item->getClass()->withTrashed()->find($id);
			};
		} catch (\Exception $e) {}

		return null;
	}

	public static function getByClassIdOnlyTrashed($classId)
	{
		if (! strpos($classId, static::ID_SEPARATOR)) return null;

		try {

			$array = explode(static::ID_SEPARATOR, $classId);
			$id = array_pop($array);
			$class = implode('\\', $array);

			$site = App::make('site');

            $item = $site->getItemByName($class);

			if ($item) {
				return $item->getClass()->onlyTrashed()->find($id);
			};

		} catch (\Exception $e) {}

		return null;
	}

	public static function getProperty(Model $element, $name)
	{
		$item = self::getItem($element);

		$property = $item->getPropertyByName($name);

		return $property->setElement($element);
	}

	public static function getParent(Model $element)
	{
		$item = static::getItem($element);

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

	public static function getParentList(Model $element)
	{
		$parents = [];
		$parentList = [];
		$exists = [];

		$count = 0;
		$parent = static::getParent($element);

		while ($count < 100 && $parent instanceof Model) {
			if (isset($exists[static::getClassId($parent)])) {
			    break;
            }

			$parents[] = $parent;
			$exists[static::getClassId($parent)] = true;
			$parent = static::getParent($parent);
			$count++;
		}

		krsort($parents);

		foreach ($parents as $parent) {
			$parentList[] = $parent;
		}

		return $parentList;
	}

	public static function setParent(Model $element, Model $parent)
	{
		$item = self::getItem($element);

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
}
