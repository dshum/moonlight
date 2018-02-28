<?php 

namespace Moonlight\Main;

trait StyleTrait 
{
	protected $homeStyles = [];
    protected $browseStyles = [];
	protected $editStyles = [];
	protected $searchStyles = [];
	protected $itemStyles = [];
	
	public function addHomeStyle($path)
	{
		if (! in_array($path, $this->homeStyles)) {
			$this->homeStyles[] = $path;
		}

		return $this;
	}
    
    public function addBrowseStyle($classId, $path)
	{
		if (! isset($this->browseStyles[$classId])) {
			$this->browseStyles[$classId] = [];
		}

		if (! in_array($path, $this->browseStyles[$classId])) {
			$this->browseStyles[$classId][] = $path;
		}

		return $this;
	}

	public function addEditStyle($classId, $path)
	{
		if (! isset($this->editStyles[$classId])) {
			$this->editStyles[$classId] = [];
		}

		if (! in_array($path, $this->editStyles[$classId])) {
			$this->editStyles[$classId][] = $path;
		}

		return $this;
	}

	public function addSearchStyle($class, $path)
	{
		if (! isset($this->searchStyles[$class])) {
			$this->searchStyles[$class] = [];
		}

		if (! in_array($path, $this->searchStyles[$class])) {
			$this->searchStyles[$class][] = $path;
		}

		return $this;
	}

	public function addItemStyle($class, $path)
	{
		if (! isset($this->itemStyles[$class])) {
			$this->itemStyles[$class] = [];
		}

		if (! in_array($path, $this->itemStyles[$class])) {
			$this->itemStyles[$class][] = $path;
		}

		return $this;
	}

	public function getHomeStyles()
	{
		return $this->homeStyles;
	}

	public function getBrowseStyles($classId)
	{
		$styles = [];

		if (isset($this->browseStyles[$classId])) {
			foreach ($this->browseStyles[$classId] as $path) {
				$styles[] = $path;
			}
		}

		if (strpos($classId, Element::ID_SEPARATOR)) {
			$parts = explode(Element::ID_SEPARATOR, $classId);
			$id = array_pop($parts);
			$class = implode(Element::ID_SEPARATOR, $parts);
		} else {
			$class = $classId;
		}

		if (isset($this->browseStyles[$class])) {
			foreach ($this->browseStyles[$class] as $path) {
				$styles[] = $path;
			}
		}

		return $styles;
	}

	public function getEditStyles($classId)
	{
		$styles = [];

		if (isset($this->editStyles[$classId])) {
			foreach ($this->editStyles[$classId] as $path) {
				$styles[] = $path;
			}
		}

		if (strpos($classId, Element::ID_SEPARATOR)) {
			$parts = explode(Element::ID_SEPARATOR, $classId);
			$id = array_pop($parts);
			$class = implode(Element::ID_SEPARATOR, $parts);
		} else {
			$class = $classId;
		}

		if (isset($this->editStyles[$class])) {
			foreach ($this->editStyles[$class] as $path) {
				$styles[] = $path;
			}
		}

		return $styles;
	}

	public function getSearchStyles($class)
	{
		$styles = [];

		if (isset($this->searchStyles[$class])) {
			foreach ($this->searchStyles[$class] as $path) {
				$styles[] = $path;
			}
		}

		return $styles;
	}

	public function getItemStyles($class)
	{
		$styles = [];

		if (isset($this->itemStyles[$class])) {
			foreach ($this->itemStyles[$class] as $path) {
				$styles[] = $path;
			}
		}

		return $styles;
	}
}