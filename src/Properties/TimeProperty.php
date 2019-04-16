<?php 

namespace Moonlight\Properties;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TimeProperty extends BaseProperty
{
	protected static $format = 'H:i:s';

	protected $fillNow = false;

	public function __construct($name) {
		parent::__construct($name);

		$this->
		addRule('date_format:"'.static::$format.'"', 'Недопустимый формат времени');

		return $this;
	}

	public static function create($name)
	{
		return new self($name);
	}

	public function setFillNow($fillNow = true)
	{
		$this->fillNow = $fillNow;

		return $this;
	}

	public function getFillNow()
	{
		return $this->fillNow;
	}

	public function format($format)
	{
		return $this->value ? $this->value->format($format) : null;
	}

	public function hour()
	{
		return $this->value ? $this->value->format('H') : null;
	}

	public function minute()
	{
		return $this->value ? $this->value->format('i') : null;
	}

	public function second()
	{
		return $this->value ? $this->value->format('s') : null;
	}

	public function setElement(Model $element)
	{
		parent::setElement($element);

		if (is_string($this->value)) {
			try {
				$this->value = Carbon::createFromTimeString($this->value);
			} catch (\Exception $e) {}
		}

		if ( ! $this->value && $this->getFillNow()) {
			$this->value = Carbon::now();
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
}
