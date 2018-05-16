<?php

namespace App\Commands\Game;

use App\Replies\Game\GameStatusReply;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Log;

class GameStatusCommand extends AbstractGameCommand
{
    /**
     * @param BotMan $bot
     */
    public function __invoke(BotMan $bot): void
    {
        Log::debug('Game status command');
        try {
            $this->setBot($bot);
            $game = $this->requireGame();
            GameStatusReply::dispatch($bot, $game);
        } catch (\Exception $e) {
            $bot->reply($e->getMessage());
            return;
        }
    }
}
