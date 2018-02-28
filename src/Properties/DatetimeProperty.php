<?php 

namespace Moonlight\Properties;

use Log;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DatetimeProperty extends BaseProperty
{
	protected $format = 'Y-m-d H:i:s';
	protected $fillNow = false;

	public function __construct($name) {
		parent::__construct($name);

		$this->
		addRule('date_format:"'.$this->format.'"', 'Недопустимый формат даты');

		return $this;
	}

	public static function create($name)
	{
		return new self($name);
	}

	public function setFillNow()
	{
		$this->fillNow = true;

		return $this;
	}

	public function getFillNow()
	{
		return $this->fillNow;
	}

	public function setElement(Model $element)
	{
		parent::setElement($element);

		if (is_string($this->value)) {
			try {
				$this->value = Carbon::createFromFormat($this->format, $this->value);
			} catch (\Exception $e) {}
		}

		if ( ! $this->value && $this->getFillNow()) {
			$this->value = Carbon::now();
		}

		return $this;
	}

	public function searchQuery($query)
	{
        $request = $this->getRequest();
		$name = $this->getName();

		$from = $request->input($name.'_from');
        $to = $request->input($name.'_to');

		if ($from) {
			try {
				$from = Carbon::createFromFormat('Y-m-d', $from);
				$query->where($name, '>=', $from->format('Y-m-d'));
			} catch (\Exception $e) {}
		}

		if ($to) {
			try {
				$to = Carbon::createFromFormat('Y-m-d', $to);
				$query->where($name, '<=', $to->format('Y-m-d'));
			} catch (\Exception $e) {}
		}

		return $query;
	}

	public function getSearchView()
	{
		$request = $this->getRequest();
        $name = $this->getName();
        $from = $request->input($name.'_from');
        $to = $request->input($name.'_to');

		try {
			$from = Carbon::createFromFormat('Y-m-d', $from);
		} catch (\Exception $e) {
			$from = null;
		}

		try {
			$to = Carbon::createFromFormat('Y-m-d', $to);
		} catch (\Exception $e) {
			$to = null;
		}

		$scope = array(
			'name' => $this->getName(),
			'title' => $this->getTitle(),
			'from' => $from,
			'to' => $to,
		);

		return $scope;
	}
    
    public function buildInput()
    {
        $request = $this->getRequest();
        $name = $this->getName();
        
        $date = $request->input($name.'_date');
        $hours = $request->input($name.'_hours');
        $minutes = $request->input($name.'_minutes');
        $seconds = $request->input($name.'_seconds');
        
        if ( ! mb_strlen($date)) $date = null;
        if ( $date === 'null') $date = null;

        $value = $date
            ? $date.' '.$hours.':'.$minutes.':'.$seconds
            : null;
        
        return $value;
    }
    
    public function set()
	{
        $name = $this->getName();
        $value = $this->buildInput();

        $this->element->$name = $value;

		return $this;
	}
}
