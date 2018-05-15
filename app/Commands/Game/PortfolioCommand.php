<?php

namespace App\Commands\Game;

use App\Replies\Game\PortfolioReply;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Log;

class PortfolioCommand extends AbstractGameCommand
{
    /**
     * @param BotMan $bot
     */
    public function __invoke(BotMan $bot): void
    {
        Log::debug('Portfolio command');
        try {
            $this->setBot($bot);
            $player = $this->requiredPlayer();
            PortfolioReply::dispatch($bot, $player);
        } catch (\Exception $e) {
            $bot->reply($e->getMessage());
            return;
        }
    }
}
