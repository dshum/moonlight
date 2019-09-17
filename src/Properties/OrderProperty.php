<?php

namespace Moonlight\Properties;

use Moonlight\Main\Item;

class OrderProperty extends BaseProperty
{
	protected $relatedClass = null;

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

	public function setItem(Item $item)
	{
		$item->setOrderProperty($this->name);

		parent::setItem($item);

		return $this;
	}

	public function getTitle()
	{
		return 'Порядок';
	}

	public function getReadonly()
	{
		return false;
	}

	public function getHidden()
	{
		return true;
	}

	public function set()
	{
		$name = $this->getName();

		try {
			$maxOrder = $this->element->max($name);
			$this->element->$name = (int)$maxOrder + 1;
		} catch (\Exception $e) {
			$this->element->$name = 1;
		}

		return $this;
	}

	public function searchQuery($query)
	{
		return $query;
	}

    public function getEditView()
	{
		return null;
	}
}
