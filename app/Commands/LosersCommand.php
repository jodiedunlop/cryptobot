<?php

namespace App\Commands;

use App\Jobs\LosersReplyJob;
use App\Models\VO\LosersRequest;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Log;

class LosersCommand extends AbstractCommand
{
    /**
     * @param BotMan $bot
     * @param string|null $period
     */
    public function __invoke(BotMan $bot, string $period = null): void
    {
        $request = new LosersRequest($period);
        Log::info('Losers command');
        $bot->reply('One sec ...');
        $bot->types();
        LosersReplyJob::dispatch($bot, $request);
    }
}