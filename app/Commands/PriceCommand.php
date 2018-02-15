<?php

namespace App\Commands;

use App\Exceptions\CoinLookupException;
use App\Jobs\PriceReplyJob;
use App\Models\Coin;
use App\Models\VO\PriceRequest;
use App\Util\PriceUtil;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Log;

class PriceCommand extends AbstractCommand
{
    /**
     * @param BotMan $bot
     * @param string $symbol
     * @param string|null|mixed $date
     */
    public function __invoke(BotMan $bot, string $symbol, $date = null): void
    {

        $symbol = PriceUtil::sanitizeSymbol($symbol);
        Log::info("Price command for symbol:{$symbol}, date:{$date}");
        try {
            $coin = $this->findCoin($symbol);
        } catch (\Exception $e) {
            $this->bot->reply($e->getMessage());
            return;
        }

        $priceRequest = new PriceRequest($coin->toValueObject());
        try {
            $userInfo = $bot->getUser()->getInfo();
            $priceRequest->parseDate($date, $userInfo['tz'] ?? null);
        } catch (\Exception $e) {
            Log::error("Unable to parse date: {$date}: ".$e->getMessage());
            $this->bot->reply('Your date makes no sense to me.');
            return;
        }
        $bot->reply('One sec ...');
        $bot->types();
        PriceReplyJob::dispatch($bot, $priceRequest);
    }

    protected function findCoin(string $symbol): Coin
    {
        $coin = null;

        foreach (['symbol', 'name'] as $field) {
            if (($coin = Coin::where($field, 'like', $symbol)->first()) !== null) {
                break;
            }
        }

        if ($coin === null) {
           throw new CoinLookupException("Can't find that coin: {$symbol}");
        }

        return $coin;
    }
}