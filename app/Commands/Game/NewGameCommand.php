<?php

namespace App\Commands\Game;

use App\Commands\AbstractCommand;
use App\Exceptions\GameException;
use App\Replies\NewGameReply;
use App\Services\GameService;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Log;

class NewGameCommand extends AbstractCommand
{
    /**
     * @param BotMan $bot
     * @param string $symbol Symbol or name of coin
     * @param string|null|mixed $date
     */
    public function __invoke(BotMan $bot): void
    {
        Log::debug('Start game command');
        try {
            /** @var GameService $gameService */
            $gameService = resolve(GameService::class);
            $platformId = $bot->getMessage()->getRecipient();
            if ($platformId[0] === 'D') {
                throw new GameException('I only accept new game requests within channels');
            }

            $user = $bot->getUser();
            $game = $gameService->startNewGame($platformId, 5000);

            $player = $gameService->addPlayerToGame($game, $user->getId(), $user->getUsername());
        } catch (\Exception $e) {
            $bot->reply($e->getMessage());
            return;
        }

//        (new NewGameReply($bot, $game, $player))->handle();
        $bot->reply(':tada: New crypto trading game started <!here>', [
            'attachments' => json_encode([
                [
                    'text' => 'Type `play` to join in, or `help` for more commands',
                    'color' => '#3AA3E3',
                ],
                [
                    'fields' => [
                        [
                            "title" => "Starting Balance",
                            "value" => "{$game->start_balance} USD",
                        ],
                        [
                            "title" => 'Time Remaining',
                            "value" => $game->finishes_at->diffForHumans(),
                        ],
                        [
                            "title" => 'Trading starts',
                            "value" => ':hourglass: '.$game->starts_at->diffForHumans(),
                        ]
                    ],
                ]
            ])
        ]);

//        NewGameReply::dispatch($bot);
    }
}
