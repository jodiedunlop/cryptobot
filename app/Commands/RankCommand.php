<?php

namespace App\Commands;

use App\Replies\RankReply;
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