<?php

namespace Moonlight\Properties;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Moonlight\Main\Item;

abstract class BaseProperty
{
    protected $item;
    protected $name;
    protected $title;
    protected $class;
    protected $itemClass;
    protected $element;
    protected $value;
    protected $request;
    protected $listViewAttribute;
    protected $show = false;
    protected $required = false;
    protected $readonly = false;
    protected $hidden = false;
    protected $editable = false;
    protected $openItem = false;
    protected $trashed = false;
    protected $rules = [];
    protected $captions = [];

    public function __construct($name)
    {
        $this->name = $name;
        $this->class = class_basename(get_class($this));

        return $this;
    }

    public function getClassName()
    {
        return class_basename($this);
    }

    public function getItem()
    {
        return $this->item;
    }

    public function setItem(Item $item)
    {
        $this->item = $item;
        $this->itemClass = $item->getClass();

        return $this;
    }

    public function getItemClass()
    {
        return $this->itemClass;
    }

    public function getShow()
    {
        return $this->show;
    }

    public function setShow($show)
    {
        $this->show = $show;

        return $this;
    }

    public function getRequired()
    {
        return $this->required;
    }

    public function setRequired($required = true)
    {
        $this->required = $required;

        if ($required) {
            $this->addRule('required', 'Поле обязательно к заполнению');
        }

        return $this;
    }

    public function getHidden()
    {
        return $this->hidden;
    }

    public function setHidden($hidden)
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function getEditable()
    {
        return false;
    }

    public function setEditable($editable)
    {
        $this->editable = $editable;

        return $this;
    }

    public function isSortable()
    {
        return true;
    }

    public function getOpenItem()
    {
        return $this->openItem;
    }

    public function setOpenItem($openItem)
    {
        $this->openItem = $openItem;

        return $this;
    }

    public function dropElement()
    {
        $this->element = null;
        $this->value = null;

        return $this;
    }

    public function getElement()
    {
        return $this->element;
    }

    public function setElement(Model $element)
    {
        $this->element = $element;
        $this->value = $element->{$this->getName()};
        $this->trashed = method_exists($element, 'trashed')
            ? $element->trashed() : false;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function setRelation(Model $relation)
    {
        return $this;
    }

    public function isTrashed()
    {
        return $this->trashed;
    }

    public function with($query)
    {
        return $query;
    }

    public function searchQuery($query)
    {
        $request = $this->getRequest();
        $name = $this->getName();

        $value = $request->input($name);

        if ($value) {
            $query->where($name, 'ilike', "%$value%");
        }

        return $query;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    public function set()
    {
        $name = $this->getName();
        $value = $this->buildInput();

        $this->element->$name = $value;

        return $this;
    }

    public function setAfterCreate()
    {
        return $this;
    }

    public function buildInput()
    {
        $request = $this->getRequest();
        $name = $this->getName();

        $value = $request->input($name);

        if (
            (is_string($value) || is_numeric($value))
            && ! mb_strlen($value)
        ) {
            $value = null;
        }

        return $value;
    }

    public function drop()
    {
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function setListViewAttribute(string $attribute)
    {
        $this->listViewAttribute = $attribute;

        return $this;
    }

    public function getListViewAttribute()
    {
        return $this->listViewAttribute;
    }

    public function getListView()
    {
        $listViewAttribute = $this->getListViewAttribute();

        $value = $listViewAttribute && $this->element->hasGetMutator($listViewAttribute)
            ? $this->element->{$listViewAttribute}
            : $this->getValue();

        return [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'value' => $value,
        ];
    }

    public function getEditView()
    {
        return [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'value' => $this->getValue(),
            'readonly' => $this->getReadonly(),
        ];
    }

    public function getReadonly()
    {
        return $this->readonly;
    }

    public function setReadonly($readonly)
    {
        $this->readonly = $readonly;

        return $this;
    }

    public function getEditableView()
    {
        return null;
    }

    public function getSearchView()
    {
        $request = $this->getRequest();
        $name = $this->getName();
        $value = $request->input($name);

        if (! mb_strlen($value)) {
            $value = null;
        }

        $scope = [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'value' => $value,
        ];

        return $scope;
    }

    public function addRule($rule, $message = null)
    {
        $this->rules[$rule] = $message;

        return $this;
    }

    public function dropRule($rule)
    {
        if (isset($this->rules[$rule])) {
            unset($this->rules[$rule]);
        }

        return $this;
    }

    public function getRules()
    {
        return $this->rules;
    }

    public function setRules(array $rules)
    {
        $this->rules = $rules;

        return $this;
    }

    public function addCaption(string $caption)
    {
        $this->captions[] = $caption;

        return $this;
    }

    public function getCaptions()
    {
        return $this->captions;
    }

    public function isMain()
    {
        return false;
    }

    public function isOrder()
    {
        return false;
    }

    public function isOneToOne()
    {
        return false;
    }

    public function isManyToMany()
    {
        return false;
    }

    public function isVirtual()
    {
        return false;
    }

    public function isShowEditable()
    {
        return true;
    }

    public function refresh()
    {
        return true;
    }
}
