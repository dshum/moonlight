<?php

namespace Moonlight\Properties;

class TextfieldProperty extends BaseProperty
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
        return [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'value' => $this->getValue(),
            'element' => $this->getElement(),
        ];
    }
}
