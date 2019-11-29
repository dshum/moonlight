<?php

namespace Moonlight\Properties;

class TinymceProperty extends BaseProperty
{
    protected $typograph = true;
    protected $toolbar = 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | subscript superscript code';

    public static function create($name)
    {
        return new self($name);
    }

    public function setTypograph($typograph)
    {
        $this->typograph = $typograph;

        return $this;
    }

    public function getTypograph()
    {
        return $this->typograph;
    }

    public function setToolbar($toolbar)
    {
        $this->toolbar = $toolbar;

        return $this;
    }

    public function getToolbar()
    {
        return $this->toolbar;
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
            'toolbar' => $this->getToolbar(),
        ];

        return $scope;
    }
}
