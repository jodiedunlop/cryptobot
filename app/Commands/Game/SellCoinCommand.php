<?php

namespace App\Commands\Game;

use App\Models\Coin;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Log;

class SellCoinCommand extends AbstractGameCommand
{
    /**
     * @param BotMan $bot
     */
    public function __invoke(BotMan $bot, $amount, $coinSymbol): void
    {
        Log::debug('Sell coin');
        try {
            $this->setBot($bot);
            $player = $this->requiredPlayer();

            // Get relevant coin
            $coin = Coin::fuzzyFindOrFail($coinSymbol);

            $playerTrade = $this->gameService()->addSell($player, $coin, $amount);
            $bot->reply("You sold $amount {$coin->symbol()}", [
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
