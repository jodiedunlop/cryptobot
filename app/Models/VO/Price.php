<?php
namespace App\Models\VO;

use Carbon\Carbon;
use App\Models\Coin;

class Price
{
    /** @var Coin */
    protected $coin;

    /** @var array */
    protected $prices;

    /** @var Carbon */
    protected $date;

    /**
     * @param Coin $coin
     * @param array $prices
     * @param Carbon|null $date
     */
    public function __construct(Coin $coin, array $prices, Carbon $date = null)
    {
        $this->setCoin($coin);
        $this->setPrices($prices);
        $this->setDate($date ?? Carbon::create());
    }

    /**
     * @param array $prices
     * @return $this
     */
    public function setPrices(array $prices)
    {
        $this->prices = $prices;

        return $this;
    }

    /**
     * @return array
     */
    public function getPrices(): array
    {
        return $this->prices;
    }

    /**
     * @param string $priceSymbol
     * @return null|string
     */
    public function getPrice(string $priceSymbol): ?string
    {
        return $this->prices[strtoupper($priceSymbol)] ?? null;
    }

    /**
     * @return string
     */
    public function symbol(): string
    {
        return $this->coin->symbol();
    }

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
        return $this->date->getTimestamp();
    }

    /**
     * @return Carbon
     */
    public function getDate(): Carbon
    {
        return $this->date;
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