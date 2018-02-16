<?php

namespace App\Services;

use App\Exceptions\CoinUpdateException;
use App\Exceptions\PriceLookupException;
use App\Models\Coin;
use App\Models\VO\Price;
use App\Models\VO\PriceRequest;
use App\Util\PriceUtil;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Zttp\Zttp;

class CoinDataService
{
    public function currentPrice(PriceRequest $priceRequest): Price
    {
        Log::debug("Looking up current price for {$priceRequest->symbol()}");

        $coin = Coin::fromValueObject($priceRequest->getCoin());
        $prices = $coin->latestPriceMap();
        Log::debug("Got prices for {$priceRequest->symbol()}:", $prices);

        return new Price($priceRequest->getCoin(), $prices, $coin->sourced_at);
    }

    public function historicalPrice(PriceRequest $priceRequest): Price
    {
        $utcDate = $priceRequest->getUtcDate();
        if ($utcDate === null) {
            throw new PriceLookupException('No date specified for historical price lookup');
        }
        if ($utcDate->greaterThan(new Carbon())) {
            throw new PriceLookupException("I can't predict the future :thonk:");
        }
        Log::debug("Looking up historical price for {$priceRequest->symbol()}", [
            'date' => $utcDate->toDateTimeString(),
        ]);

        // Ensure it's a valid coin
        $coin = Coin::fromValueObject($priceRequest->getCoin());

        // See if there's a price collected before or equal to the requested date,
        // but not 5 minutes older than that
        $priceQuery = $coin->pricesAt($utcDate);

        // We don't have an existing historical price, so fetch one
        if (!$priceQuery->count()) {
            $data = Zttp::get('https://min-api.cryptocompare.com/data/pricehistorical', [
                'fsym' => $priceRequest->symbol(),
                'tsyms' => 'BTC,USD,AUD',
                'ts' => $priceRequest->getTimestamp(),
            ])->json();

            Log::debug('Response from historical price:', $data);
            if (isset($data['Response']) && $data['Response'] === 'Error') {
                throw new PriceLookupException((string)$data->Response);
            }

            // Prices are indexed on the symbol name
            if (!isset($data[$priceRequest->symbol()])) {
                throw new PriceLookupException("Response didn't contain symbol, so that's weird");
            }
            /** @var array $priceData */
            $priceData = $data[$priceRequest->symbol()];
            Log::debug("Got price data for {$priceRequest->symbol()}:", $priceData);
            foreach ($priceData as $currencySymbol => $price) {
                $coin->prices()->create([
                    'price' => $price,
                    'currency_symbol' => $currencySymbol,
                    'sourced_at' => $utcDate,
                ]);
            }
        }

        $priceMap = $coin->priceMap($priceQuery);
        Log::debug('Converted into price map:', $priceMap);
        return new Price($priceRequest->getCoin(), $priceMap, $priceRequest->getDate());
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
     * @throws \App\Exceptions\CoinUpdateException
     * @throws CoinUpdateException
     */
    public function updateCoinList(): int
    {
        Log::info('Updating coin list');
        $data = Zttp::get('https://min-api.cryptocompare.com/data/all/coinlist')->json();
        if (!isset($data['Response']) || $data['Response'] !== 'Success') {
            throw new CoinUpdateException($data['Message'] ?? 'Payload did not contain a Response=Success parameter');
        }

        $coinsUpdated = 0;
        foreach ($data['Data'] as $key => $val) {
            Coin::updateOrCreate(['remote_id' => 'cc:' . $val['Id']], [
                'name' => $val['CoinName'],
                'full_name' => $val['FullName'],
                'symbol' => PriceUtil::sanitizeSymbol($val['Symbol']),
                'image_url' => !empty($val['ImageUrl']) ? $data['BaseImageUrl'] . $val['ImageUrl'] : null,
                'info_url' => !empty($val['Url']) ? $data['BaseLinkUrl'] . $val['Url'] : null,
                'algorithm' => $val['Algorithm'] ?? 'N/A',
                'proof_type' => $val['ProofType'] ?? 'N/A',
//                'total_supply' => $val['TotalCoinSupply'] !== 'N/A' ? (int)$val['TotalCoinSupply'] : null,
                'is_premined' => $val['FullyPremined'] === '1',
                'premined_value' => $val['PreMinedValue'] ?? 'N/A',
                'total_free_float' => $val['TotalCoinsFreeFloat'] ?? 'N/A',
                'is_trading' => $val['IsTrading'] === true,
            ]);
        }
        Log::info("Updated {$coinsUpdated} coins");

        return $coinsUpdated;
    }

    public function updateCoinPrices($max = 1000)
    {
        // TODO: Chunk the fetches
        // TODO: Allow specifying a list of symbol conversions in method param (merge)
        $data = Zttp::get('https://api.coinmarketcap.com/v1/ticker/', [
            'convert' => 'AUD',
            'limit' => $max,
        ])->json();

        $sourced_at = new Carbon(null, 'UTC');
        $numCoinsUpdated = 0;
        foreach ($data as $coinData) {
            /** @var Coin $coin */
            $coin = Coin::findBySymbol($coinData['symbol']);
            if ($coin === null) {
                Log::warning('Cannot update coin price, not in database:', $coinData);
                continue;
            }
            Log::debug("Updating coin data and prices for {$coin->symbol}", $coinData);

            // Update coin info
            $coin->rank = (int)$coinData['rank'];
            $coin->total_supply = !empty($coinData['total_supply']) ? (int)$coinData['total_supply'] : 0;
            $coin->available_supply = !empty($coinData['available_supply']) ? (int)$coinData['available_supply'] : 0;
            $coin->max_supply = !empty($coinData['max_supply']) ? (int)$coinData['max_supply'] : 0;
            $coin->volume_usd_24h = !empty($coinData['24h_volume_usd']) ? (int)$coinData['24h_volume_usd'] : 0;
            $coin->market_cap_usd = !empty($coinData['market_cap_usd']) ? (int)$coinData['market_cap_usd'] : 0;
            $coin->percent_change_1h = !empty($coinData['percent_change_1h']) ? (float)$coinData['percent_change_1h'] : 0;
            $coin->percent_change_24h = !empty($coinData['percent_change_24h']) ? (float)$coinData['percent_change_24h'] : 0;
            $coin->percent_change_7d = !empty($coinData['percent_change_7d']) ? (float)$coinData['percent_change_7d'] : 0;
            $coin->sourced_at = $sourced_at;
            $coin->save();

            // Add prices
            foreach (['BTC', 'USD', 'AUD'] as $currencySymbol) {
                $priceKey = strtolower("price_{$currencySymbol}");
                if (isset($coinData[$priceKey])) {
                    $coinPrice = $coin->prices()->create([
                        'currency_symbol' => PriceUtil::sanitizeSymbol($currencySymbol),
                        'price' => $coinData[$priceKey],
                    ]);
                    Log::debug("Coin price added to {$coin->symbol}", $coinPrice->toArray());
                }
            }
            $numCoinsUpdated++;
        }
        return $numCoinsUpdated;
    }

    protected function getPriceRequestCacheKey($prefix, PriceRequest $priceRequest)
    {
        return sprintf('%s_%s_%s', $prefix, $priceRequest->symbol(), $priceRequest->getTimestamp());
    }
}