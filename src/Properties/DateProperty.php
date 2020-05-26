<?php

namespace Moonlight\Properties;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DateProperty extends BaseProperty
{
    protected $format = 'Y-m-d';
    protected $fillNow = false;

    public function __construct($name)
    {
        parent::__construct($name);

        $this->addRule('date_format:"'.$this->format.'"', 'Недопустимый формат даты');

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

    public function setElement(Model $element)
    {
        parent::setElement($element);

        if (is_string($this->value)) {
            $this->value = Carbon::createFromFormat($this->format, $this->value);
        }

        if (! $this->value && $this->getFillNow()) {
            $this->value = Carbon::today();
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
            $from = Carbon::createFromFormat('Y-m-d', $from);
            $query->where($name, '>=', $from->format('Y-m-d'));
        }

        if ($to) {
            $to = Carbon::createFromFormat('Y-m-d', $to);
            $query->where($name, '<=', $to->format('Y-m-d'));
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
            $from = $from ? Carbon::createFromFormat('Y-m-d', $from) : null;
        } catch (Exception $e) {
            $from = null;
        }

        try {
            $to = $to ? Carbon::createFromFormat('Y-m-d', $to) : null;
        } catch (Exception $e) {
            $to = null;
        }

        return [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'from' => $from,
            'to' => $to,
        ];
    }
}
