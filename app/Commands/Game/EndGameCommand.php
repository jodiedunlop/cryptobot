<?php

namespace App\Commands\Game;

use App\Commands\AbstractCommand;
use App\Exceptions\GameException;
use App\Replies\NewGameReply;
use App\Services\GameService;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use Illuminate\Support\Facades\Log;

class EndGameCommand extends AbstractCommand
{
    /**
     * @param BotMan $bot
     * @param string $symbol Symbol or name of coin
     * @param string|null|mixed $date
     */
    public function __invoke(BotMan $bot): void
    {
        Log::debug('End game command');
        try {
            /** @var GameService $gameService */
            $gameService = resolve(GameService::class);
            $gameService->endCurrentGame();
        } catch (\Exception $e) {
            $bot->reply($e->getMessage());
            return;
        }

        $bot->reply("Game ended!");
    }
}
