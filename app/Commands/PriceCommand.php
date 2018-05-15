<?php

namespace App\Commands;

use App\Jobs\PriceReplyJob;
use App\Models\Coin;
use App\Models\VO\PriceRequest;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Log;

class PriceCommand extends AbstractCommand
{
    /**
     * @param BotMan $bot
     * @param string $symbol Symbol or name of coin
     * @param string|null|mixed $date
     */
    public function __invoke(BotMan $bot, string $symbol, $date = null): void
    {
        Log::debug("Price command for symbol:{$symbol}, date:{$date}");
        try {
            $coin = Coin::fuzzyFindOrFail($symbol);
        } catch (\Exception $e) {
            $bot->reply($e->getMessage());
            return;
        }

        $priceRequest = new PriceRequest($coin);
        try {
            $userInfo = $bot->getUser()->getInfo();
        } catch (\Exception $e) {
            Log::error("Can't fetch user info: ".$e->getMessage());
        }

        try {
            $priceRequest->parseDate($date, $userInfo['tz'] ?? 'Australia/Brisbane');
        } catch (\Exception $e) {
            Log::error("Unable to parse date: {$date}: ".$e->getMessage());
            $bot->reply('Your date makes no sense to me.');
            return;
        }
        $bot->types();
        $bot->reply('One sec ...');
        PriceReplyJob::dispatch($bot, $priceRequest);
    }
}
