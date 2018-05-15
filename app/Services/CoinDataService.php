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
    public const COIN_LIST_URL = 'https://www.cryptocompare.com/api/data/coinlist/';
    //public const COIN_LIST_URL = ''https://min-api.cryptocompare.com/data/all/coinlist';

    public $priceSymbols = ['BTC', 'USD', 'ETH'];


    public function currentPrice(PriceRequest $priceRequest): Price
    {
        Log::debug("Looking up current price for {$priceRequest->symbol()}");

        $coin = $priceRequest->getCoin();
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
        $coin = $priceRequest->getCoin();

        // See if there's a price collected before or equal to the requested date,
        // but not 5 minutes older than that
        $priceQuery = $coin->pricesAt($utcDate);

        // We don't have an existing historical price, so fetch one
        if (!$priceQuery->count()) {
            $data = Zttp::get('https://min-api.cryptocompare.com/data/pricehistorical', [
                'fsym' => $priceRequest->symbol(),
                'tsyms' => implode(',', $this->priceSymbols),
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

        $data = Cache::remember('coin-list', 5, function() {
            return Zttp::get(self::COIN_LIST_URL)->json();
        });

        if (!isset($data['Data'])) {
            Log::error('Bad coin list response from cryptocompare:', $data);
            Cache::forget('coin-list');
            throw new CoinUpdateException($data['Message'] ?? 'Payload did not contain a Response=Success parameter');
        }

        $coinsUpdated = 0;
        foreach ($data['Data'] as $key => $val) {
            Coin::updateOrCreate(['cc_id' => (int)$val['Id']], $this->coinCreationData($val));
        }
        Log::info("Updated {$coinsUpdated} coins");

        return $coinsUpdated;
    }

    public function updateCoinPrices($max = 1000)
    {
        // TODO: Chunk the fetches
        // TODO: Allow specifying a list of symbol conversions in method param (merge)
        $data = Zttp::get('https://api.coinmarketcap.com/v1/ticker/', [
            'convert' => 'ETH',
            'limit' => $max,
        ])->json();

        $sourced_at = new Carbon(null, 'UTC');
        $numCoinsUpdated = 0;
        foreach ($data as $coinData) {
            $coin = $this->lookupCoinFromCmcData($coinData);
            if ($coin === null) {
                Log::warning('Cannot update coin price, not in database:', $coinData);
                continue;
            }
            Log::debug("Updating coin data and prices for {$coin->symbol}", $coinData);

            // Update coin info
            $coin->cmc_id = (string)$coinData['id'];
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
            foreach ($this->priceSymbols as $currencySymbol) {
                $priceKey = strtolower("price_{$currencySymbol}");
                if (isset($coinData[$priceKey])) {
                    $coinPrice = $coin->prices()->create([
                        'currency_symbol' => PriceUtil::sanitizeSymbol($currencySymbol),
                        'price' => $coinData[$priceKey],
                        'sourced_at' => $sourced_at,
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

    /**
     * Lookup Coin model from CoinMarketCap coin data
     * @param array $coinData
     * @return Coin|null
     */
    protected function lookupCoinFromCmcData(array $coinData): ?Coin
    {
        // Try and find coin by ID first
        $coin = Coin::where('cmc_id', $coinData['id'])->first();
        if ($coin !== null) {
            return $coin;
        }

        // Search by symbol
        $coin = Coin::where('symbol', PriceUtil::sanitizeSymbol($coinData['symbol']))
            ->whereNull('cmc_id')
            ->first();
        if ($coin !== null) {
            return $coin;
        }

        // Search by name
        $coin = Coin::where('name', $coinData['name'])
            ->whereNull('cmc_id')
            ->first();

        return $coin;
    }

    public function coinCreationData(array $val)
    {
        // Get the crypto-compare ID
        $ccId = (int)$val['Id'];

        // Coin creation data
        $data = [
            'name' => $val['CoinName'],
            'full_name' => $val['FullName'],
            'symbol' => PriceUtil::sanitizeSymbol($val['Symbol']),
            'image_url' => null,
            'info_url' => null,
            'algorithm' => $val['Algorithm'] ?? 'N/A',
            'proof_type' => $val['ProofType'] ?? 'N/A',
            'is_premined' => $val['FullyPremined'] === '1',
            'premined_value' => $val['PreMinedValue'] ?? 'N/A',
            'total_free_float' => $val['TotalCoinsFreeFloat'] ?? 'N/A',
            'is_trading' => $val['IsTrading'] ?? true,
        ];

        switch ($ccId) {
            case 347235:
                // Bitcoin gold
                $data['cmc_id'] = 'bitcoin-gold';
                $data['symbol'] = 'BTG';
                break;
            case 4402:
                // Bitgem
                $data['cmc_id'] = 'bitgem';
                break;
            case 127356:
                // Iota
                $data['cmc_id'] = 'iota';
                $data['symbol'] = 'IOTA';
                break;
            case 218008:
                // Bytom
                $data['cmc_id'] = 'bytom';
                break;
            case 310497:
                // Kyber network
                $data['cmc_id'] = 'kyber-network';
                break;
            case 199901:
                // Smart cash
                $data['cmc_id'] = 'smartcash';
                break;
            case 1217209:
                // Nano
                $data['cmc_id'] = 'nano';
                $data['symbol'] = 'NANO';
                break;
            case 299397:
                // Walton chain
                $data['cmc_id'] = 'waltonchain';
                $data['symbol'] = 'WTC';
                break;
//            case 000:
//                // XPA
//                $data['cmc_id'] = 'xpa';
//                $data['symbol'] = 'XPA';
//                break;
//            case 000:
//                // Bibox Token
//                $data['cmc_id'] = 'bibox-token';
//                break;
//            case 000:
//                // All Sports
//                $data['cmc_id'] = 'all-sports';
//                break;
//            case 000:
//                // Delphy
//                $data['cmc_id'] = 'delphy';
//                $data['symbol'] = 'SOC';
//                break;
            case 35165:
                // Bitconnect
                $data['cmc_id'] = 'bitconnect';
                $data['symbol'] = 'BCC';
                break;
            default:
                break;
        }

        return $data;
    }
}
