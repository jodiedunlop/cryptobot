<?php

namespace App\Commands;

use App\Jobs\GainersReplyJob;
use App\Models\VO\GainersRequest;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Log;

class GainersCommand extends AbstractCommand
{
    /**
     * @param BotMan $bot
     * @param string|null $period
     */
    public function __invoke(BotMan $bot, string $period = null): void
    {
        $request = new GainersRequest($period);
        Log::info('Gainers command');
        $bot->reply('One sec ...');
        $bot->types();
        GainersReplyJob::dispatch($bot, $request);
    }
}