<?php

namespace Moonlight\Properties;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DatetimeProperty extends BaseProperty
{
    protected $format = 'Y-m-d H:i:s';
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
            } catch (Exception $e) {
            }
        }

        if ($to) {
            try {
                $to = Carbon::createFromFormat('Y-m-d', $to);
                $query->where($name, '<=', $to->format('Y-m-d'));
            } catch (Exception $e) {
            }
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

    public function buildInput()
    {
        $request = $this->getRequest();
        $name = $this->getName();

        $date = $request->input($name.'_date');
        $time = $request->input($name.'_time');

        if (! mb_strlen($date) || $date === 'null') {
            $date = null;
        }

        return $date ? $date.' '.$time : null;
    }
}
