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

    public function toValueObject()
    {
        return new \App\Models\VO\Coin($this->symbol, [
            'name' => $this->name,
            'full_name' => $this->full_name,
            'image_url' => $this->image_url,
            'info_url' => $this->info_url,
            'percent_change_1h' => $this->percent_change_1h,
            'percent_change_24h' => $this->percent_change_24h,
            'percent_change_7d' => $this->percent_change_7d,
            'rank' => $this->rank,
        ]);
    }

    public static function fromValueObject(\App\Models\VO\Coin $coin): Coin
    {
        if ($coin->has('id')) {
            return static::findOrFail($coin->id);
        }
        return static::findBySymbolOrFail($coin->symbol());

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
    public function pricesAt(Carbon $date, int $windowMinutes = 5)
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
    public static function find(string $str): ?Coin
    {
        $coin = static::findBySymbol($str);
        if ($coin === null) {
            // Try by name
            $coin = Coin::where('name', $str)->first();
        }
        if ($coin === null) {
            // Try by name
            $coin = Coin::where('name', 'like', $str.'%')
                ->orderBy(DB::raw('LENGTH(name)'), 'DESC')
                ->first();
        }

        return $coin;
    }

    public static function findOrFail(string $str): Coin
    {
        $coin = static::find($str);
        if ($coin === null) {
            throw new ModelNotFoundException("Can't find that coin: '{$str}''");
        }

        return $coin;
    }
}
