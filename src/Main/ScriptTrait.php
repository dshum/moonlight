<?php 

namespace Moonlight\Main;

trait ScriptTrait 
{
	protected $homeScripts = [];
	protected $browseScripts = [];
	protected $editScripts = [];
	protected $searchScripts = [];
	protected $itemScripts = [];
	
	public function addHomeScript($path)
	{
		if (! in_array($path, $this->homeScripts)) {
			$this->homeScripts[] = $path;
		}

		return $this;
	}
    
    public function addBrowseScript($classId, $path)
	{
		if (! isset($this->browseScripts[$classId])) {
			$this->browseScripts[$classId] = [];
		}

		if (! in_array($path, $this->browseScripts[$classId])) {
			$this->browseScripts[$classId][] = $path;
		}

		return $this;
	}

	public function addEditScript($classId, $path)
	{
		if (! isset($this->editScripts[$classId])) {
			$this->editScripts[$classId] = [];
		}

		if (! in_array($path, $this->editScripts[$classId])) {
			$this->editScripts[$classId][] = $path;
		}

		return $this;
	}

	public function addSearchScript($class, $path)
	{
		if (! isset($this->searchScripts[$class])) {
			$this->searchScripts[$class] = [];
		}

		if (! in_array($path, $this->searchScripts[$class])) {
			$this->searchScripts[$class][] = $path;
		}

		return $this;
	}

	public function addItemScript($class, $path)
	{
		if (! isset($this->itemScripts[$class])) {
			$this->itemScripts[$class] = [];
		}

		if (! in_array($path, $this->itemScripts[$class])) {
			$this->itemScripts[$class][] = $path;
		}

		return $this;
	}

	public function getHomeScripts()
	{
		return $this->homeScripts;
	}

	public function getBrowseScripts($classId)
	{
		$scripts = [];

		if (isset($this->browseScripts[$classId])) {
			foreach ($this->browseScripts[$classId] as $path) {
				$scripts[] = $path;
			}
		}

		if (strpos($classId, Element::ID_SEPARATOR)) {
			$parts = explode(Element::ID_SEPARATOR, $classId);
			$id = array_pop($parts);
			$class = implode(Element::ID_SEPARATOR, $parts);
		} else {
			$class = $classId;
		}

		if (isset($this->browseScripts[$class])) {
			foreach ($this->browseScripts[$class] as $path) {
				$scripts[] = $path;
			}
		}

		return $scripts;
	}

	public function getEditScripts($classId)
	{
		$scripts = [];

		if (isset($this->editScripts[$classId])) {
			foreach ($this->editScripts[$classId] as $path) {
				$scripts[] = $path;
			}
		}

		if (strpos($classId, Element::ID_SEPARATOR)) {
			$parts = explode(Element::ID_SEPARATOR, $classId);
			$id = array_pop($parts);
			$class = implode(Element::ID_SEPARATOR, $parts);
		} else {
			$class = $classId;
		}

		if (isset($this->editScripts[$class])) {
			foreach ($this->editScripts[$class] as $path) {
				$scripts[] = $path;
			}
		}

		return $scripts;
	}

	public function getSearchScripts($class)
	{
		$scripts = [];

		if (isset($this->searchScripts[$class])) {
			foreach ($this->searchScripts[$class] as $path) {
				$scripts[] = $path;
			}
		}

		return $scripts;
	}

	public function getItemScripts($class)
	{
		$scripts = [];

		if (isset($this->itemScripts[$class])) {
			foreach ($this->itemScripts[$class] as $path) {
				$scripts[] = $path;
			}
		}

		return $scripts;
	}
}