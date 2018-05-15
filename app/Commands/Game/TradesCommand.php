<?php

namespace App\Commands\Game;

use App\Replies\Game\TradesReply;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Log;

class TradesCommand extends AbstractGameCommand
{
    /**
     * @param BotMan $bot
     */
    public function __invoke(BotMan $bot): void
    {
        Log::debug('Trades command');
        try {
            $this->setBot($bot);
            $player = $this->requiredPlayer();
            TradesReply::dispatch($bot, $player);
        } catch (\Exception $e) {
            $bot->reply($e->getMessage());
            return;
        }
    }
}
