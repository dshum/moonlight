<?php 

namespace Moonlight\Properties;

use Moonlight\Main\Item;
use Moonlight\Main\Element;

class MainProperty extends BaseProperty 
{
	public static function create($name)
	{
		return new self($name);
    }

    public function setItem(Item $item)
	{
		$item->setMainProperty($this->name);

		parent::setItem($item);

		return $this;
	}

    public function getShow()
    {
        return true;
    }
    
    public function set()
	{
        $name = $this->getName();
        $value = $this->buildInput();

        if ($value) {
            $this->element->$name = $value;
        } else {
            $this->element->$name = $this->element->id
                ? Element::getClassId($this->element)
                : 'Element';
        }

		return $this;
    }

    public function searchQuery($query)
	{
        $request = $this->getRequest();
		$name = $this->getName();

        $id = (int)$request->input($name);
        $text = $request->input($name.'_autocomplete');

        if ($id) {
            $query->where('id', $id);
        } elseif ($text) {
            $query->where($name, 'ilike', "%$text%");
        }

		return $query;
	}
    
    public function getListView()
	{
		$element = $this->getElement();
		$classId = $element ? Element::getClassId($element) : null;

		$scope = [
            'name' => $this->getName(),
			'title' => $this->getTitle(),
			'value' => $this->getValue(),
			'classId' => $classId,
			'trashed' => $this->isTrashed(),
		];

		return $scope;
    }
    
    public function getSearchView()
	{
        $site = \App::make('site');
        
		$request = $this->getRequest();
        $name = $this->getName();
        $class = $this->getItemClass();
        $item = $this->getItem();
        $mainProperty = $item->getMainProperty();

        $id = (int)$request->input($name);
        $text = $request->input($name.'_autocomplete');
        
        $element = $id 
            ? $class::find($id)
            : null;

        $scope = array(
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'id' => $id,
            'text' => $text,
            'open' => $element !== null,
            'relatedClass' => $item->getNameId(),
        );

		return $scope;
	}
}
