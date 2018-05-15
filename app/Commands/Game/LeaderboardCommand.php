<?php

namespace App\Commands\Game;

use App\Replies\Game\LeaderboardReply;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Log;

class LeaderboardCommand extends AbstractGameCommand
{
    /**
     * @param BotMan $bot
     */
    public function __invoke(BotMan $bot): void
    {
        Log::debug('Leaderboard command');
        try {
            $this->setBot($bot);
            $game = $this->requireGame();
            LeaderboardReply::dispatch($bot, $game);
        } catch (\Exception $e) {
            $bot->reply($e->getMessage());
            return;
        }
    }
}
