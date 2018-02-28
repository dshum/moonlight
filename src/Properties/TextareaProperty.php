<?php 

namespace Moonlight\Properties;

class TextareaProperty extends BaseProperty
{
	public static function create($name)
	{
		return new self($name);
	}

	public function getEditable()
	{
		return $this->editable;
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
