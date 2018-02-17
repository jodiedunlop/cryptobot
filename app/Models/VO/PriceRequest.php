<?php
namespace App\Models\VO;

use App\Util\PriceUtil;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PriceRequest
{
    /** @var Coin */
    protected $coin;

    /** @var array */
    protected $props;

    /** @var string|null */
    protected $timezone;

    /** @var Carbon|null */
    protected $date;

    public function __construct(Coin $coin, Carbon $date = null, array $props = [])
    {
        $this->setCoin($coin);
        if ($date !== null) {
            $this->setDate($date);
        }

        $this->setProps($props);
    }

    public function setProps(array $props)
    {
        $this->props = $props;

        return $this;
    }

    public function setProp(string $key, $val)
    {
        $this->props[$key] = $val;

        return $this;
    }

    public function getProp(string $key, $default = null)
    {
        return $this->props[$key] ?? $default;
    }

    public function getProps(): array
    {
        return $this->props;
    }

    public function symbol(): string
    {
        return $this->coin->symbol();
    }

    public function hasDate(): bool
    {
        return $this->date !== null;
    }

    /**
    /**
     * @param int $timestamp
     * @param string $tz
     * @return $this
     */
    protected function setTimestamp(int $timestamp, string $tz = 'UTC')
    {
        $this->date = Carbon::createFromTimestamp($timestamp, $tz);

        return $this;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->hasDate() ? $this->date->getTimestamp() : 0;
    }

    /**
     * @return Carbon|null
     */
    public function getDate(): ?Carbon
    {
        return $this->date;
    }

    public function getUtcDate(): ?Carbon
    {
        $utcDate = null;
        if (($date = $this->getDate()) !== null) {
            $utcDate = Carbon::createFromTimestampUTC($date->timestamp);
        }

        return $utcDate;
    }

    /**
     * @param Carbon $date
     * @return $this
     */
    public function setDate(Carbon $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @param string $str
     * @param string|null $tz
     * @return $this
     */
    public function parseDate($str, $tz = null)
    {
        if (empty($tz)) {
            $tz = 'UTC';
        }
        if (!empty($str)) {
            $date = Carbon::parse($str, $tz);
            Log::info("Set date to $str $tz");
            $this->setDate($date);
        }

        return $this;
    }

    public function setCoin(Coin $coin)
    {
        $this->coin = $coin;

        return $this;
    }

    public function getCoin(): Coin
    {
        return $this->coin;
    }
}