<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Moonlight\Main\Item;

if (! function_exists('class_id')) {
    function class_id(Model $element)
    {
        return App::make('site')->getClassId($element);
    }
}

if (! function_exists('get_item')) {
    function item_id(Model $element)
    {
        return App::make('site')->getItemByElement($element);
    }
}

if (! function_exists('item_id')) {
    function item_name(Model $element)
    {
        $item = App::make('site')->getItemByElement($element);

        return $item instanceof Item ? $item->getName() : null;
    }
}

if (! function_exists('property')) {
    function property(Model $element, $propertyName)
    {
        $item = App::make('site')->getItemByElement($element);

        return $item instanceof Item
            ? $item->getPropertyByName($propertyName)->setElement($element)
            : null;
    }
}
