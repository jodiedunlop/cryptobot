<?php

namespace App\Commands;

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
            /** @var Coin $coin */
            $coin = Coin::where('symbol', $symbol)->firstOrFail();
        } catch (\Exception $e) {
            $this->bot->reply("Invalid coin symbol $symbol");
            return;
        }

        $priceRequest = new PriceRequest($coin->toValueObject());
        try {
            $userInfo = $bot->getUser()->getInfo();
            $priceRequest->parseDate($date, $userInfo['tz'] ?? null);
        } catch (\Exception $e) {
            $this->bot->reply('Your date makes no sense to me.'.$e->getMessage());
            return;
        }
        $bot->reply('One sec ...');
        $bot->types();
        PriceReplyJob::dispatch($bot, $priceRequest);
    }
}