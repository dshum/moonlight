<?php 

namespace Moonlight\Main;

use Moonlight\Properties\OrderProperty;
use Moonlight\Properties\DatetimeProperty;
use Moonlight\Properties\BaseProperty;

class Rubric 
{
    protected $name = null;
    protected $title = null;
    protected $binds = [];

    public function __construct($name, $title) {
        $this->name = $name;
        $this->title = $title;

        return $this;
    }

    public static function create($name, $title)
    {
        return new self($name, $title);
    }

    public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	public function getName()
	{
		return $this->name;
    }

    public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}

	public function getTitle()
	{
		return $this->title;
    }

    public function getBinds()
    {
        return $this->binds;
    }

    public function getBindByName($name = 0)
    {
        return isset($this->binds[$name])
            ? $this->binds[$name]
            : null;
    }

    public function bind($first, $addition = null, $name = 0)
	{
        $this->binds[$name] = [
            'first' => $first,
            'addition' => $addition,
        ];

		return $this;
	}
}