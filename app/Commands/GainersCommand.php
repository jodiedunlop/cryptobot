<?php

namespace App\Commands;

use App\Models\VO\GainersRequest;
use App\Replies\GainersReply;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Log;

class GainersCommand extends AbstractCommand
{
    /**
     * @param BotMan $bot
     * @param string|null $period
     * @param int $limit
     */
    public function __invoke(BotMan $bot, string $period = null, int $limit = 10): void
    {
        $request = new GainersRequest(
            $period ?? GainersRequest::PERIOD_24HRS,
            $limit
        );
        Log::info('Rank command');
        (new GainersReply($bot))->send($request);
    }
}