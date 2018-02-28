<?php 

namespace Moonlight\Properties;

use Illuminate\Database\Eloquent\Model;

class VirtualProperty extends BaseProperty 
{
	public static function create($name)
	{
		return new self($name);
	}

	public function isSortable()
	{
		return false;
	}

	public function setElement(Model $element)
	{
		$this->element = $element;

		$getter = $this->getter();

		$this->value = $element->$getter();

		return $this;
	}

	public function set()
	{
		return $this;
	}
    
    public function searchQuery($query)
	{
		return $query;
	}

	public function getSearchView()
	{
		return null;
	}
}
