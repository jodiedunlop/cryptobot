<?php

namespace App\Commands;

use App\Jobs\GainersImageReplyJob;
use App\Models\VO\GainersRequest;
use App\Replies\GainersImageReply;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Log;

class GainersImageCommand extends AbstractCommand
{
    /**
     * @param BotMan $bot
     * @param string|null $period
     * @param int $limit
     * @throws \Spatie\Browsershot\Exceptions\CouldNotTakeBrowsershot
     */
    public function __invoke(BotMan $bot, string $period = null, int $limit = 10): void
    {
        $request = new GainersRequest(
            $period ?? GainersRequest::PERIOD_24H,
            $limit
        );
        Log::info('Gainers image command');
        $bot->reply('One sec ...');
        $bot->types();
        GainersImageReplyJob::dispatch($bot, $request);
    }
}