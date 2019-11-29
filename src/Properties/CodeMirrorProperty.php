<?php

namespace Moonlight\Properties;

class CodeMirrorProperty extends BaseProperty
{
    public static function create($name)
    {
        return new self($name);
    }

    public function refresh()
    {
        return false;
    }

    public function getEditView()
    {
        $scope = [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'value' => $this->getValue(),
            'readonly' => $this->getReadonly(),
        ];

        return $scope;
    }
}
