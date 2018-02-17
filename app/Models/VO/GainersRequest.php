<?php
namespace App\Models\VO;

class GainersRequest
{
    public const PERIOD_1HR = '1hr';
    public const PERIOD_24HRS = '24hrs';
    public const PERIOD_7DAYS = '7days';

    protected $period;
    protected $limit;
    protected $minRank;
    protected $maxRank;

    public function __construct($period, $limit = 10, $minRank = 1, $maxRank = 200)
    {
        $this->period = $period;
        $this->limit = $limit;
        $this->minRank = $minRank;
        $this->maxRank = $maxRank;
    }

    public function getPeriod()
    {
        return $this->period;
    }

    public function getPeriodDescription()
    {
        $desc = '';
        switch ($this->getPeriod()) {
            case static::PERIOD_1HR:
                $desc = 'last hour';
                break;
            case static::PERIOD_24HRS:
                $desc = 'last 24 hours';
                break;
            case static::PERIOD_7DAYS:
                $desc = 'last 7 days';
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