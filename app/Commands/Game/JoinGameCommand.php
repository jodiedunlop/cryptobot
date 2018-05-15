<?php

namespace App\Commands\Game;

use App\Commands\AbstractCommand;
use App\Exceptions\GameException;
use App\Services\GameService;
use BotMan\BotMan\BotMan;
use Illuminate\Support\Facades\Log;

class JoinGameCommand extends AbstractCommand
{
    /**
     * @param BotMan $bot
     */
    public function __invoke(BotMan $bot): void
    {
        Log::debug('Join game');
        try {
            /** @var GameService $gameService */
            $gameService = resolve(GameService::class);
            $game = $gameService->getCurrentGame();
            if ($game === null) {
                throw new GameException('There is no current game, type: `start game` if you want to start one');
            }
            $user = $bot->getUser();
            $player = $gameService->addPlayerToGame($game, $user->getId(), $user->getUsername());
        } catch (\Exception $e) {
            $bot->reply($e->getMessage());
            return;
        }

        Log::info('Joined game: '.$player->name);
        // TODO: Move these commands out of here.
        $bot->reply(
            "@{$player->name} you're in the game!\n".
            'You currently have $' .$player->getTransactionBalance()." USD funds available.\n"
        );


        Log::debug('User: ' .$bot->getUser()->getUsername());
//        Log::debug('User info', $bot->getUser()->getInfo());

        $bot->reply('Cool, so '.$bot->getUser()->getUsername().' is in.');
        Log::debug('Message sender '.$bot->getMessage()->getSender());
    }
}
