<?php 

namespace Moonlight\Properties;

class PasswordProperty extends BaseProperty
{
	public static function create($name)
	{
		return new self($name);
	}

	public function isSortable()
	{
		return false;
	}

	public function set()
	{   
		$name = $this->getName();
		$value = $this->buildInput();

		if ($this->getHidden() || $this->getReadonly()) {
			if (! $this->element->$name) {
				$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
				$value = substr(str_shuffle($chars), 0, 8);

				$this->element->$name = password_hash($value, PASSWORD_DEFAULT);
			}
		} else {
			if ($value) {
				$this->element->$name = password_hash($value, PASSWORD_DEFAULT);
			}
		}

		return $this;
	}
    
    public function searchQuery($query)
	{
		return $query;
	}

	public function getSearchView()
	{
		return null;
	}
    
    public function getListView()
    {
        return null;
    }
}
