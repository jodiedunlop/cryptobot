<?php

namespace App\Services;

use App\Exceptions\CoinUpdateException;
use App\Exceptions\PriceLookupException;
use App\Models\Coin;
use App\Models\VO\Price;
use App\Models\VO\PriceRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Zttp\Zttp;

class CoinDataService
{
    public function currentPrice(PriceRequest $priceRequest): Price
    {
        Log::debug('Looking up current price', [
            'symbol' => $priceRequest->symbol(),
            'date' => $priceRequest->hasDate() ?
                $priceRequest->getDate()->toDateTimeString() :
                '<None>',
        ]);
        $cacheKey = $this->getPriceRequestCacheKey('price', $priceRequest);
        $prices = Cache::remember($cacheKey, 0.5, function () use ($priceRequest) {
            $prices = Zttp::get('https://min-api.cryptocompare.com/data/price', [
                'fsym' => $priceRequest->symbol(),
                'tsyms' => 'BTC,USD,AUD',
            ])->json();
            Log::debug('Response from price:', $prices);
            if (isset($prices['Response']) && $prices['Response'] === 'Error') {
                throw new PriceLookupException($prices->Response);
            }
            return $prices;
        });

        return new Price($priceRequest->getCoin(), $prices, $priceRequest->getDate());
    }

    public function historicalPrice(PriceRequest $priceRequest): Price
    {
        Log::debug('Looking up historical price', [
            'symbol' => $priceRequest->symbol(),
            'date' => $priceRequest->hasDate() ?
                $priceRequest->getDate()->toDateTimeString() :
                '<None>',
        ]);

        $cacheKey = $this->getPriceRequestCacheKey('historical_price', $priceRequest);
        $prices = Cache::remember($cacheKey, 0.5, function () use ($priceRequest) {
            $prices = Zttp::get('https://min-api.cryptocompare.com/data/pricehistorical', [
                'fsym' => $priceRequest->symbol(),
                'tsyms' => 'BTC,USD,AUD',
                'ts' => $priceRequest->getTimestamp(),
            ])->json();

            Log::debug('Response from historical price:', $prices);
            if (isset($prices['Response']) && $prices['Response'] === 'Error') {
                throw new PriceLookupException((string)$prices->Response);
            }

            // Prices are indexed on the symbol name
            if (!isset($prices[$priceRequest->symbol()])) {
                throw new PriceLookupException("Response didn't contain symbol, so that's weird");
            }
            $prices = $prices[$priceRequest->symbol()];
            return $prices;
        });

        return new Price($priceRequest->getCoin(), $prices, $priceRequest->getDate());
    }

    /**
     * @param PriceRequest $priceRequest
     * @return Price
     * @throws \Exception
     */
    public function price(PriceRequest $priceRequest): Price
    {
        if ($priceRequest->hasDate()) {
            // Timestamp provided, get a historical price
            return $this->historicalPrice($priceRequest);
        }

        return $this->currentPrice($priceRequest);
    }

    /**
     * @throws CoinUpdateException
     */
    public function updateCoinList(): int
    {
        $data = Cache::remember('coin_list', 0.5, function () {
            $data = Zttp::get('https://min-api.cryptocompare.com/data/all/coinlist')->json();
            if (!isset($data['Response']) || $data['Response'] !== 'Success') {
                throw new CoinUpdateException($data['Message'] ?? 'Payload did not contain a Response=Success parameter');
            }
            return $data;
        });

        $coinsUpdated = 0;
        foreach ($data['Data'] as $key => $val) {
            Coin::updateOrCreate(['remote_id' => 'cc:'.$val['Id']], [
                'name' => $val['CoinName'],
                'full_name' => $val['FullName'],
                'symbol' => $val['Symbol'],
                'image_url' => !empty($val['ImageUrl']) ? $data['BaseImageUrl'] . $val['ImageUrl'] : null,
                'info_url' => !empty($val['Url']) ? $data['BaseLinkUrl'] . $val['Url'] : null,
                'algorithm' => $val['Algorithm'] ?? 'N/A',
                'proof_type' => $val['ProofType'] ?? 'N/A',
                'total_supply' => $val['TotalCoinSupply'] !== 'N/A' ? (int)$val['TotalCoinSupply'] : null,
                'is_premined' => $val['FullyPremined'] === '1',
                'premined_value' => $val['PreMinedValue'] ?? 'N/A',
                'total_free_float' => $val['TotalCoinsFreeFloat'] ?? 'N/A',
                'is_trading' => $val['IsTrading'] === true,
            ]);
        }

        return $coinsUpdated;
    }

    protected function getPriceRequestCacheKey($prefix, PriceRequest $priceRequest)
    {
        return sprintf('%s_%s_%s', $prefix, $priceRequest->symbol(), $priceRequest->getTimestamp());
    }
}