<?php

namespace App\Commands;

use App\Exceptions\CoinLookupException;
use App\Jobs\PriceReplyJob;
use App\Models\Coin;
use App\Models\VO\PriceRequest;
use App\Replies\RankReply;
use App\Util\PriceUtil;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Log;

class RankCommand extends AbstractCommand
{
    /**
     * @param BotMan $bot
     * @param int $limit
     */
    public function __invoke(BotMan $bot, $limit = null): void
    {
        Log::info('Rank command');
        (new RankReply($bot))->send();
    }
}