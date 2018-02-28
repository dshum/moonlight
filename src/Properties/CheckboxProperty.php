<?php 

namespace Moonlight\Properties;

use Illuminate\Database\Eloquent\Model;

class CheckboxProperty extends BaseProperty {

	public static function create($name)
	{
		return new self($name);
	}

	public function setElement(Model $element)
	{
		$this->element = $element;

		$value = $element->{$this->getName()};

		$this->value = $value ? true : false;

		return $this;
	}

	public function getEditable()
	{
		return $this->editable;
	}

	public function searchQuery($query)
	{
        $request = $this->getRequest();
        $name = $this->getName();

		$value = $request->input($name);

		if ($value === 'true') {
			$query->where($name, 1);
		} elseif ($value === 'false') {
			$query->where($name, 0);
		}

		return $query;
	}

	public function set()
	{
		$request = $this->getRequest();
        $name = $this->getName();

		$value = $request->has($name) && $request->input($name)
			? true : false;

		$this->element->$name = $value;

		return $this;
	}

	public function getEditableView()
	{
		$scope = array(
			'name' => $this->getName(),
			'title' => $this->getTitle(),
			'value' => $this->getValue(),
			'element' => $this->getElement(),
		);

		return $scope;
	}
}
