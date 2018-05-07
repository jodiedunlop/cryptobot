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
     * @param string $symbol Symbol or name of coin
     * @param string|null|mixed $date
     */
    public function __invoke(string $symbol, $date = null): void
    {
        $this->bot = app('botman');
        Log::info("Price command for symbol:{$symbol}, date:{$date}");
        try {
            $coin = Coin::findOrFail($symbol);
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
}