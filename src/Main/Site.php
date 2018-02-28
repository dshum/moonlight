<?php 

namespace Moonlight\Main;

class Site 
{
	const ROOT = 'Root';
	const TRASH = 'Trash';
	const SEARCH = 'Search';

	protected $items = [];
	protected $binds = [];
	protected $rubrics = [];
	protected $bindsTree = [];

	protected $homePlugin = null;
	protected $itemPlugins = [];
	protected $browsePlugins = [];
	protected $browseFilters = [];
	protected $searchPlugins = [];
	protected $editPlugins = [];

	protected $initMicroTime = null;

	use StyleTrait;
	use ScriptTrait;

	public function addRubric(Rubric $rubric)
	{
		$name = $rubric->getName();

		$this->rubrics[$name] = $rubric;

		return $this;
	}

	public function getRubricList()
	{
		return $this->rubrics;
	}

	public function getRubricByName($name)
	{
		return
			isset($this->rubrics[$name])
			? $this->rubrics[$name]
			: null;
	}

	public function addItem(Item $item)
	{
		$name = $item->getName();

		$this->items[$name] = $item;

		return $this;
	}

	public function getItemList()
	{
		return $this->items;
	}

	public function getItemByName($name)
	{
		$name = str_replace(Element::ID_SEPARATOR, '\\', $name);

		return
			isset($this->items[$name])
			? $this->items[$name]
			: null;
	}

	public function bind($parent, $binds)
	{
        if (is_array($binds)) {
            foreach ($binds as $bind) {
                $this->binds[$parent][$bind] = $bind;
            }
        } elseif (is_string($binds)) {
            $this->binds[$parent][$binds] = $binds;
        }

		return $this;
	}

	public function getBinds()
	{
		return $this->binds;
	}

	public function addHomePlugin($plugin)
	{
		$this->homePlugin = $plugin;

		return $this;
	}

	public function getHomePlugin()
	{
		if ($this->homePlugin) {
			return $this->homePlugin;
		}

		return null;
	}

	public function addItemPlugin($class, $plugin)
	{
		$this->itemPlugins[$class] = $plugin;

		return $this;
	}

	public function getItemPlugins()
	{
		return $this->itemPlugins;
	}

	public function getItemPlugin($class)
	{
		if (isset($this->itemPlugins[$class])) {
			return $this->itemPlugins[$class];
		}

		return null;
	}

	public function addBrowsePlugin($classId, $plugin)
	{
		$this->browsePlugins[$classId] = $plugin;

		return $this;
	}

	public function getBrowsePlugins()
	{
		return $this->browsePlugins;
	}

	public function getBrowsePlugin($classId)
	{
		if (isset($this->browsePlugins[$classId])) {
			return $this->browsePlugins[$classId];
		}

		if (strpos($classId, Element::ID_SEPARATOR)) {
			$parts = explode(Element::ID_SEPARATOR, $classId);
			$id = array_pop($parts);
			$class = implode(Element::ID_SEPARATOR, $parts);
		} else {
			$class = $classId;
		}

		if (isset($this->browsePlugins[$class])) {
			return $this->browsePlugins[$class];
		}

		return null;
	}

	public function addSearchPlugin($class, $plugin)
	{
		$this->searchPlugins[$class] = $plugin;

		return $this;
	}

	public function getSearchPlugins()
	{
		return $this->searchPlugins;
	}

	public function getSearchPlugin($class)
	{
		if (isset($this->searchPlugins[$class])) {
			return $this->searchPlugins[$class];
		}

		return null;
	}

	public function addEditPlugin($classId, $plugin)
	{
		$this->editPlugins[$classId] = $plugin;

		return $this;
	}

	public function getEditPlugins()
	{
		return $this->editPlugins;
	}

	public function getEditPlugin($classId)
	{
		if (isset($this->editPlugins[$classId])) {
			return $this->editPlugins[$classId];
		}

		if (strpos($classId, Element::ID_SEPARATOR)) {
			$parts = explode(Element::ID_SEPARATOR, $classId);
			$id = array_pop($parts);
			$class = implode(Element::ID_SEPARATOR, $parts);
		} else {
			$class = $classId;
		}

		if (isset($this->editPlugins[$class])) {
			return $this->editPlugins[$class];
		}

		return null;
	}

	public function addBrowseFilter($class, $plugin)
	{
		$this->browseFilters[$class] = $plugin;

		return $this;
	}

	public function getBrowseFilters()
	{
		return $this->browseFilters;
	}

	public function getBrowseFilter($class)
	{
		if (isset($this->browseFilters[$class])) {
			return $this->browseFilters[$class];
		}

		return null;
	}

	public function end()
	{
		return $this;
	}

	public function initMicroTime()
	{
		$this->initMicroTime = explode(' ', microtime());
	}

	public function getMicroTime()
	{
		list($usec1, $sec1) = explode(' ', microtime());
		list($usec0, $sec0) = $this->initMicroTime;

		$time = (float)$sec1 + (float)$usec1 - (float)$sec0 - (float)$usec0;

		return round($time, 6);
	}

	public function getMemoryUsage()
	{
		return round(memory_get_peak_usage() / 1024 / 1024, 2);
	}
}
