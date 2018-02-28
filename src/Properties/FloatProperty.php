<?php 

namespace Moonlight\Properties;

class FloatProperty extends BaseProperty
{
	public function __construct($name) {
		parent::__construct($name);

		$this->
		addRule('numeric', 'Введите число с запятой');

		return $this;
	}

	public static function create($name)
	{
		return new self($name);
	}

	public function getEditable()
	{
		return $this->editable;
	}

	public function searchQuery($query)
	{
        $request = $this->getRequest();
		$name = $this->getName();

		$from = $request->input($name.'_from');
        $to = $request->input($name.'_to');

		if (mb_strlen($from)) {
			$from = str_replace(array(',', ' '), array('.', ''), $from);
			$query->where($name, '>=', (double)$from);
		}

		if (strlen($to)) {
			$to = str_replace(array(',', ' '), array('.', ''), $to);
			$query->where($name, '<=', (double)$to);
		}

		return $query;
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

	public function getSearchView()
	{
		$request = $this->getRequest();
        $name = $this->getName();
        
        $from = $request->input($name.'_from');
        $to = $request->input($name.'_to');

		if ( ! mb_strlen($from)) $from = null;
		if ( ! mb_strlen($to)) $to = null;

		$scope = array(
			'name' => $this->getName(),
			'title' => $this->getTitle(),
			'from' => $from,
            'to' => $to,
		);

		return $scope;
	}
}