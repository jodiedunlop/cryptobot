<?php

namespace App\Models;

use App\Util\PriceUtil;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Coin extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'total_supply' => 'integer',
        'available_supply' => 'integer',
        'max_supply' => 'integer',
        'volume_usd_24h' => 'integer',
        'market_cap_usd' => 'integer',
        'rank' => 'integer',
        'percent_change_1h' => 'float',
        'percent_change_24h' => 'float',
        'percent_change_7d' => 'float',
        'is_premined' => 'boolean',
        'is_trading' => 'boolean',
    ];

    protected $dates = [
        'sourced_at',
    ];

    /**
     * Scope a query to only include popular users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $period
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGainers($query, $period = '24h')
    {
        $changeColumn = sprintf('percent_change_%s', $period);
        return $query->where('rank', '!=', 0)
            ->where('rank', '<', 200)
            ->where($changeColumn, '>', 0)
            ->orderBy($changeColumn, 'DESC');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(CoinPrice::class);
    }

    public function latestPrices()
    {
        // Get prices which have the same source date
        return $this->prices()->where('sourced_at', $this->sourced_at);
    }

    /**
     * @param Carbon $date
     * @param int $windowMinutes
     * @return Builder
     */
    public function pricesAt(Carbon $date, int $windowMinutes = 5): Builder
    {
        return $this->prices()
            ->where('sourced_at', '<=', $date)
            ->where('sourced_at', '>=', $date->subMinutes($windowMinutes));
    }

    public function latestPriceMap(): array
    {
        return $this->priceMap($this->latestPrices());
    }

    public function priceFor($currencySymbol)
    {
        $map = $this->priceMap($this->latestPrices());
        return $map[PriceUtil::sanitizeSymbol($currencySymbol)] ?? 0;
    }

    public function priceMap($priceQuery)
    {
        $map = [];
        foreach ($priceQuery->get() as $coinPrice) {
            $map[$coinPrice->currency_symbol] = $coinPrice->price ?? 0;
        }

        return $map;
    }

    public function symbol()
    {
        return PriceUtil::sanitizeSymbol($this->attributes['symbol']);
    }

    public function thumbUrl()
    {
        return $this->image_url ??
            'https://raw.githubusercontent.com/cjdowner/cryptocurrency-icons/master/128/color/'.
            strtolower($this->symbol()).
            '.png';
    }


    /**
     * @param string $symbol
     * @return null|Coin
     */
    public static function findBySymbol(string $symbol): ?Coin
    {
        return static::where('symbol', PriceUtil::sanitizeSymbol($symbol))
            ->first();
    }

    /**
     * @param string $symbol
     * @return Coin
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function findBySymbolOrFail(string $symbol): Coin
    {
        if (($coin = static::findBySymbol($symbol)) === null) {
            throw new ModelNotFoundException("Unable to find Coin by symbol {$symbol}");
        }
        return $coin;
    }

    /**
     * @param string $str
     * @return Coin|null
     */
    public static function fuzzyFind(string $str): ?Coin
    {
        $coin = static::findBySymbol($str);
        if ($coin === null) {
            // Try by name
            $coin = static::where('name', $str)->first();
        }
        if ($coin === null) {
            // Try by name
            $coin = static::where('name', 'like', $str.'%')
                ->orderBy(DB::raw('LENGTH(name)'), 'DESC')
                ->first();
        }

        return $coin;
    }

    public static function fuzzyFindOrFail(string $str): Coin
    {
        $coin = static::fuzzyFind($str);
        if ($coin === null) {
            throw new ModelNotFoundException("Can't find that coin: '{$str}''");
        }

        return $coin;
    }
}
