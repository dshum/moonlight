<?php

namespace Moonlight\Properties;

use Illuminate\Support\Facades\App;
use Moonlight\Main\Item;

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
        $item = $this->getItem();
        $value = $this->buildInput();

        if ($value) {
            $this->element->$name = $value;
        } else {
            $this->element->$name = $this->element->id
                ? $item->getTitle().' '.$this->element->id
                : $item->getTitle();
        }

        return $this;
    }

    public function searchQuery($query)
    {
        $request = $this->getRequest();
        $name = $this->getName();

        $id = (int) $request->input($name);
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
        $site = App::make('site');
        $element = $this->getElement();

        return [
            'value' => $this->getValue(),
            'class_id' => $site->getClassId($element),
            'trashed' => $this->isTrashed(),
        ];
    }

    public function getSearchView()
    {
        $request = $this->getRequest();
        $name = $this->getName();
        $class = $this->getItemClass();
        $item = $this->getItem();

        $id = (int) $request->input($name);
        $text = $request->input($name.'_autocomplete');

        $element = $id ? $class::find($id) : null;

        return [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'id' => $id,
            'text' => $text,
            'open' => $element !== null,
            'relatedItem' => $item,
        ];
    }

    public function isMain()
    {
        return true;
    }

    public function isShowEditable()
    {
        return false;
    }
}
