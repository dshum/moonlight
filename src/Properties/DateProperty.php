<?php 

namespace Moonlight\Properties;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DateProperty extends BaseProperty
{
	protected $format = 'Y-m-d';
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
			$this->value = Carbon::today();
		}
        
        if ($this->value) {
            $this->value = $this->value->format('c');
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
}
