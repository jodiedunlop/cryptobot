<?php
/**
 * Created by PhpStorm.
 * User: jd
 * Date: 15/5/18
 * Time: 12:07 PM
 */

namespace App\Commands\Game;


use App\Exceptions\GameException;
use App\Models\Game;
use App\Models\Player;
use App\Services\GameService;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Interfaces\UserInterface;

abstract class AbstractGameCommand
{
    protected $bot;

    /** @var GameService|null */
    protected $gameService;

    /** @var Game|null */
    protected $game;

    /** @var UserInterface|null */
    protected $user;

    /** @var Player|null */
    protected $player;

    protected function setGameService(GameService $gameService)
    {
        $this->gameService = $gameService;
        return $this;
    }

    protected function gameService(): GameService
    {
        if ($this->gameService === null) {
            $this->gameService = resolve(GameService::class);
        }
        return $this->gameService;
    }

    protected function setBot(BotMan $bot)
    {
        $this->bot = $bot;
    }

    protected function getBot()
    {
        return $this->bot;
    }

    public function requireBot(): BotMan
    {
        if (!$this->bot) {
            throw new \RuntimeException('No BotMan instance set on command, call setBot() first');
        }

        return $this->bot;
    }

    protected function getUser(): UserInterface
    {
        if ($this->user === null) {
            $this->user = $this->requireBot()->getUser();
        }

        return $this->user;
    }

    protected function getPlayer()
    {
        if ($this->player === null) {
            $this->player = Player::where('platform_id', $this->getUser()->getId())
                ->where('game_id', $this->requireGame()->id)
                ->first();
        }

        return $this->player;
    }

    protected function requiredPlayer(): Player
    {
        $player = $this->getPlayer();
        if ($player === null) {
            throw new GameException('You are not registered for the game. Type: `play` to join in.');
        }

        return $player;
    }

    /**
     * @return Game|null
     */
    protected function getGame()
    {
        if ($this->game === null) {
            $this->game = $this->gameService()->getCurrentGame();
        }

        return $this->game;
    }

    protected function requireGame(): Game
    {
        $game = $this->getGame();
        if ($game === null) {
            throw new GameException('There is no current game, but you can start one with `new game`');
        }

        return $game;
    }
}