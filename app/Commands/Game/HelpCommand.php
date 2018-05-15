<?php

namespace App\Commands\Game;

use App\Commands\AbstractCommand;
use App\Replies\Game\AvailableCommandsReply;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Log;

class HelpCommand extends AbstractCommand
{
    /**
     * @param BotMan $bot
     */
    public function __invoke(BotMan $bot): void
    {
        AvailableCommandsReply::dispatch($bot);
    }
}
