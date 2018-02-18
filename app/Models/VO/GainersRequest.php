<?php
namespace App\Models\VO;

class GainersRequest
{
    public const PERIOD_1H = '1h';
    public const PERIOD_24H = '24h';
    public const PERIOD_7D = '7d';

    protected $period;
    protected $limit;
    protected $minRank;
    protected $maxRank;

    public function __construct($period, $limit = null, $minRank = null, $maxRank = 200)
    {
        $this->period = $period;
        $this->limit = $limit ?? 10;
        $this->minRank = $minRank ?? 1;
        $this->maxRank = $maxRank ?? 200;
    }

    public function getPeriod()
    {
        $period = '24h';
        if ($this->period && preg_match('/^(1h|24h|7d)$/i', $this->period)) {
            $period = strtolower($this->period);
        }

        return $period;
    }

    public function getPeriodDescription()
    {
        $desc = '';
        switch ($this->getPeriod()) {
            case static::PERIOD_1H:
                $desc = 'hour';
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

    public function getMinRank(): int
    {
        return $this->minRank;
    }

    public function getMaxRank(): int
    {
        return $this->maxRank;
    }
}