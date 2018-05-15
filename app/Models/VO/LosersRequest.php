<?php
namespace App\Models\VO;

class LosersRequest
{
    public const PERIOD_1H = '1h';
    public const PERIOD_24H = '24h';
    public const PERIOD_7D = '7d';

    protected $period;
    protected $limit;
    protected $min;
    protected $max;

    public function __construct($period = null, $limit = null, $min = null, $max = 200)
    {
        $this->setPeriod($period);
        $this->limit = $limit ?? 10;
        $this->min = $min ?? 1;
        $this->max = $max ?? 200;
    }

    public function setPeriod($period)
    {
        $period = trim($period);
        if (preg_match('/(1|last)\s?h(r|our)?s?/i', $period)) {
            $this->period = self::PERIOD_1H;
        } elseif (preg_match('/24\s?h(r|our)?s?/i', $period)) {
            $this->period = self::PERIOD_24H;
        } elseif (preg_match('/7\s?d(ay)?s?/i', $period)) {
            $this->period = self::PERIOD_7D;
        } else {
            $this->period = self::PERIOD_24H;
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPeriod()
    {
        return $this->period;
    }

    public function getPeriodDescription(): string
    {
        $desc = '';
        switch ($this->getPeriod()) {
            case static::PERIOD_1H:
                $desc = '1 hour';
                break;
            case static::PERIOD_24H:
                $desc = '24 hours';
                break;
            case static::PERIOD_7D:
                $desc = '7 days';
                break;
            default:
                break;
        }

        return $desc;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getMin(): int
    {
        return $this->min;
    }

    public function getMax(): int
    {
        return $this->max;
    }
}