<?php

use Illuminate\Database\Eloquent\Model;
use Moonlight\Main\Element;

if (! function_exists('class_id')) {
    function class_id(Model $element)
    {
        return Element::getClassId($element);
    }
}

if (! function_exists('item_id')) {
    function item_id(Model $element)
    {
        $item = Element::getItem($element);
        
        return $item->getNameId();
    }
}

if (! function_exists('property')) {
    function property(Model $element, $propertyName)
    {
        $item = Element::getItem($element);
        $property = $item->getPropertyByName($propertyName);

        $property->setElement($element);

        return $property;
    }
}