<?php 

namespace Moonlight\Main;

use Illuminate\Database\Eloquent\Model;
use Moonlight\Main\ElementInterface;
use Moonlight\Properties\FileProperty;
use Moonlight\Properties\ImageProperty;
use Moonlight\Properties\OrderProperty;

final class Element 
{
	const ID_SEPARATOR = '.';

	public static function getItem(Model $element)
	{
		$site = \App::make('site');

		$class = static::getClass($element);

		return $site->getItemByName($class);
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
				Element::getClass($element)
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
            
            $site = \App::make('site');
            
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

			$site = \App::make('site');
            
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

			$site = \App::make('site');
            
            $item = $site->getItemByName($class);

			if ($item) {
				return $item->getClass()->onlyTrashed()->find($id);
			};

		} catch (\Exception $e) {}

		return null;
	}

	public static function getProperty(Model $element, $name)
	{
		$item = Element::getItem($element);

		$property = $item->getPropertyByName($name);

		return $property->setElement($element);
	}

	public static function equalTo($element1, $element2)
	{
		return
			$element1 instanceof Model
			&& $element2 instanceof Model
			&& static::getClassId($element1) === static::getClassId($element2)
			? true 
			: false;
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
			if (isset($exists[static::getClassId($parent)])) break;
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
		$item = Element::getItem($element);

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

	public static function copy(Model $element)
	{
		$item = Element::getItem($element);

		$propertyList = $item->getPropertyList();

		$clone = new $element;

		foreach ($propertyList as $propertyName => $property) {
			if ($property instanceof OrderProperty) {
				$property->setElement($clone)->set();
				continue;
			}

			if (
				$property->getHidden()
				|| $property->getReadonly()
			) continue;

			if (
				(
					$property instanceof FileProperty
					|| $property instanceof ImageProperty
				)
				&& ! $property->getRequired()
			) continue;

			$clone->$propertyName = $element->$propertyName;
		}

		$clone->save();

		\Cache::tags(Element::getClass($element))->flush();

		return $clone;
	}

	public static function delete(Model $element)
	{
		$site = \App::make('site');

		$class = Element::getClass($element);

		$itemList = $site->getItemList();

		foreach ($itemList as $item) {
			$itemName = $item->getName();
			$propertyList = $item->getPropertyList();

			foreach ($propertyList as $property) {
				if (
					$property->isOneToOne()
					&& $property->getRelatedClass() == $class
				) {
					$count = $element->
						hasMany($itemName, $property->getName())->
						count();

					if ($count) return false;
				}
			}
		}

		$element->delete();

		\Cache::tags(Element::getClass($element))->flush();

		\Cache::forget("getByClassId({Element::getClassId($element)})");

		\Cache::forget("getWithTrashedByClassId({Element::getClassId($element)})");

		\Cache::forget("getOnlyTrashedByClassId({Element::getClassId($element)})");

		return true;
	}

	public static function deleteFromTrash(ElementInterface $element)
	{
		$item = Element::getItem($element);

		$propertyList = $item->getPropertyList();

		foreach ($propertyList as $propertyName => $property) {
			$property->setElement($element)->drop();
		}

		$element->forceDelete();

		\Cache::tags(Element::getClass($element))->flush();

		\Cache::forget("getByClassId({Element::getClassId($element)})");

		\Cache::forget("getWithTrashedByClassId({Element::getClassId($element)})");

		\Cache::forget("getOnlyTrashedByClassId({Element::getClassId($element)})");

		return true;
	}

	public static function restore(Model $element)
	{
		$element->restore();

		\Cache::tags(Element::getClass($element))->flush();

		\Cache::forget("getByClassId({Element::getClassId($element)})");

		\Cache::forget("getWithTrashedByClassId({Element::getClassId($element)})");

		\Cache::forget("getOnlyTrashedByClassId({Element::getClassId($element)})");

		return true;
	}

	public static function save(Model $element)
	{
		$element->save();

		\Cache::tags(Element::getClass($element))->flush();

		\Cache::forget("getByClassId({Element::getClassId($element)})");

		\Cache::forget("getWithTrashedByClassId({Element::getClassId($element)})");

		\Cache::forget("getOnlyTrashedByClassId({Element::getClassId($element)})");

		return true;
	}
}