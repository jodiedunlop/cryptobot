<?php

namespace App\Commands\Game;

use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Log;

class FundsCommand extends AbstractGameCommand
{
    /**
     * @param BotMan $bot
     */
    public function __invoke(BotMan $bot): void
    {
        Log::debug('Funds command');
        try {
            $this->setBot($bot);
            $player = $this->requiredPlayer();
            $bot->reply('Your current funds are '.$player->getTransactionBalance().' USD');
        } catch (\Exception $e) {
            $bot->reply($e->getMessage());
            return;
        }
    }
}
