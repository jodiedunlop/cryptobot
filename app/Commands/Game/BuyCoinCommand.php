<?php

namespace App\Commands\Game;

use App\Commands\Game\AbstractGameCommand;
use App\Exceptions\GameException;
use App\Models\Coin;
use App\Models\Player;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Log;

class BuyCoinCommand extends AbstractGameCommand
{
    /**
     * @param BotMan $bot
     */
    public function __invoke(BotMan $bot, $amount, $coinSymbol): void
    {
        Log::debug('Buy coin');
        try {
            $this->setBot($bot);
            $player = $this->requiredPlayer();

            // Get relevant coin
            $coin = Coin::fuzzyFindOrFail($coinSymbol);

            $playerTrade = $this->gameService()->addBuy($player, $coin, $amount);
            $bot->reply("You bought $amount {$coin->symbol()}", [
                    'attachments' => json_encode([
                        [
                            'text' => $playerTrade->description,
                        ]
                    ])
            ]);
        } catch (\Exception $e) {
            $bot->reply($e->getMessage());
            return;
        }
    }
}
