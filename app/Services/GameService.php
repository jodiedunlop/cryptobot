<?php

namespace App\Services;

use App\Exceptions\GameAlreadyRunningException;
use App\Exceptions\GameException;
use App\Exceptions\GameNotActiveException;
use App\Exceptions\TradeException;
use App\Models\Coin;
use App\Models\Game;
use App\Models\Player;
use App\Models\PlayerTransaction;
use App\Models\Support\TradeType;
use App\Models\PlayerTrade;
use App\Models\Support\TransactionType;
use App\Models\VO\PriceRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GameService
{

    public function list(array $filters = []): Builder
    {
        return Game::query();
    }

    public function create(array $props = []): Game
    {
        return Game::create($props);
    }

    /**
     * Start a new game if one is not already running.
     * @param mixed $gamePlatformId
     * @param int $startBalance
     * @param Carbon|null $finishAt
     * @return Game
     * @throws GameAlreadyRunningException
     */
    public function startNewGame($gamePlatformId, int $startBalance, Carbon $finishAt = null): Game
    {
        if (($existingGame = $this->getCurrentGame()) !== null) {
            throw new GameAlreadyRunningException("Can't start a new game, the current game finishes " . $existingGame->finishes_at->diffForHumans());
        }

        if ($finishAt === null) {
            $finishAt = Carbon::now()->addDays(2);
        }

        $game = $this->create([
            'platform_id' => $gamePlatformId,
            'start_balance' => $startBalance,
            'starts_at' => Carbon::now()->addMinutes(2),  // Some time for players to register
            'finishes_at' => $finishAt,
        ]);

        Log::info('Game started', $game->toArray());

        return $game;
    }

    /**
     * @param Game $game
     * @param mixed $userPlatformId
     * @param $playerName
     * @throws GameNotActiveException
     */
    public function addPlayerToGame(Game $game, $userPlatformId, string $playerName): Player
    {
        $this->checkCanAddPlayer($game, $userPlatformId, $playerName);

        // Create new player
        $player = $this->createPlayer([
            'game_id' => $game->id,
            'platform_id' => $userPlatformId,
            'name' => $playerName,
        ]);

        // Give player some starting funds
        $this->addTransaction(
            $player,
            TransactionType::DEPOSIT,
            $game->start_balance,
            'Initial deposit of ' . $game->start_balance . ' USD'
        );

        // The first player becomes the starting player
        if (!$game->started_by_player_id) {
            $game->started_by_player_id = $player->id;
            $game->save();
        }

        Log::info("Player added to game {$game->id}", $player->toArray());

        return $player;
    }

    public function createPlayer(array $props = []): Player
    {
        return Player::create($props);
    }

    /**
     * @param Player $player
     * @param Coin $coin
     * @param string $typeId
     * @param mixed $amount
     * @param string|null $description
     * @return PlayerTrade
     * @throws GameNotActiveException
     */
    public function addTrade(
        Player $player,
        Coin $coin,
        string $typeId,
        $amount,
        ?string $description = null
    ): PlayerTrade {
        if ($typeId === TradeType::BUY) {
            // Buys must be a positive amount
            if ($amount < 0) {
                throw new TradeException('Buy trades must be a positive transaction amount');
            }
        } elseif ($typeId === TradeType::SELL) {
            // Sells must be a negative amount
            if ($amount > 0) {
                throw new TradeException('Sell trades must be a negative transaction amount');
            }
        }

        $playerTrade = PlayerTrade::create([
            'player_id' => $player->id,
            'coin_id' => $coin->id,
            'type_id' => $typeId,
            'amount' => $amount,
            'description' => $description,
        ]);
        Log::info('New player trade', $playerTrade->toArray());
        return $playerTrade;
    }

    /**
     * @param Player $player
     * @param Coin $coin
     * @param $buyAmount
     * @return PlayerTrade
     * @throws GameNotActiveException
     * @throws TradeException
     */
    public function addBuy(Player $player, Coin $coin, $buyAmount): PlayerTrade
    {
        $this->checkCanAddTrade($player->game);
        $this->checkCanAddTransaction($player->game);

        $buyAmount = abs($buyAmount);

        // Determine the price of the amount to buy
        $coinPrice = $this->getPriceOfCoin($coin);
        $totalAmount = $coinPrice * $buyAmount;

        // Check that the user has enough funds to buy
        $availableAmount = $this->getTransactionBalance($player);
        if ($totalAmount > $availableAmount) {
            $shortAmount = $totalAmount - $availableAmount;
            throw new TradeException("You don't have enough funds to buy {$buyAmount} {$coin->symbol()} @{$coinPrice} - you are \${$shortAmount} short");
        }

        // Debit the account
        $this->addTransaction(
            $player,
            TransactionType::WITHDRAW,
            0 - $totalAmount,
            "Buy {$buyAmount} {$coin->symbol()} @{$coinPrice}"
        );

        // Create the asset trade
        $trade = $this->addTrade(
            $player,
            $coin,
            TradeType::BUY,
            $buyAmount,
            "Buy {$buyAmount} {$coin->symbol()} for {$totalAmount}"
        );

        return $trade;
    }

    /**
     * @param Player $player
     * @param Coin $coin
     * @param $sellAmount
     * @return PlayerTrade
     * @throws GameNotActiveException
     * @throws TradeException
     */
    public function addSell(Player $player, Coin $coin, $sellAmount): PlayerTrade
    {
        $this->checkCanAddTrade($player->game);
        $this->checkCanAddTransaction($player->game);

        $sellAmount = abs($sellAmount);

        // Check that the user has enough of this coin to sell
        $availableAmount = $this->getCoinBalance($player, $coin);
        if ($sellAmount > $availableAmount) {
            throw new TradeException("You only have {$availableAmount} {$coin->symbol()} available to sell");
        }

        // Determine the price of the amount to sell
        $coinPrice = $this->getPriceOfCoin($coin);
        $totalAmount = $coinPrice * $sellAmount;

        // Execute the trade to sell
        $trade = $this->addTrade(
            $player,
            $coin,
            TradeType::SELL,
            0 - $sellAmount,
            "Sell {$sellAmount} {$coin->symbol()} for {$totalAmount}"
        );

        // Credit the account
        $this->addTransaction(
            $player,
            TransactionType::DEPOSIT,
            $totalAmount,
            "Sell {$sellAmount} {$coin->symbol()} @{$coinPrice}"
        );

        return $trade;
    }


    /**
     * @param Player $player
     * @param string $typeId
     * @param mixed $amount
     * @param string|null $description
     * @return PlayerTransaction
     * @throws GameNotActiveException
     */
    public function addTransaction(Player $player, string $typeId, $amount, $description = null): PlayerTransaction
    {
        $playerTransaction = PlayerTransaction::create([
            'player_id' => $player->id,
            'type_id' => $typeId,
            'amount' => $amount,
            'description' => $description,
        ]);

        Log::info('New player transaction', $playerTransaction->toArray());
        return $playerTransaction;
    }

    /**
     * @param Player $player
     * @return mixed
     */
    public function getTransactionBalance(Player $player)
    {
        return $player->getTransactionBalance();
    }

    /**
     * @param Player $player
     * @param Coin $coin
     * @return mixed
     */
    public function getCoinBalance(Player $player, Coin $coin)
    {
        return $player->trades()
            ->where('coin_id', $coin->id)
            ->sum('amount');
    }

    /**
     * @param Player $player
     * @return Collection
     */
    public function getPlayerPortfolio(Player $player): Collection
    {
        $portfolio = [];

        /** @var \Illuminate\Database\Eloquent\Collection $coinAmounts */
        $coinAmounts = DB::table('player_trades')
            ->select('coin_id', DB::raw('SUM(amount) as total'))
            ->where('player_id', $player->id)
            ->groupBy('coin_id')
            ->having('total', '>', 0)
            ->get();
        foreach ($coinAmounts as $coinAmount) {
            if (($coin = Coin::find($coinAmount->coin_id)) !== null) {
                $coinPrice = $this->getPriceOfCoin($coin);
                $portfolio[] = [
                    'coin' => $coin,
                    'price' => $coinPrice,
                    'amount' => $coinAmount->total,
                    'value' => $coinPrice * $coinAmount->total,
                ];
            }
        }

        return collect($portfolio)->sortByDesc('value', SORT_NUMERIC);
    }

    public function getLeaderboard(Game $game): Collection
    {
        $leaderboard = [];

        /** @var Player $player */
        foreach ($game->players as $player) {
            $entry['player'] = $player;
            $entry['player_name'] = $player->name;
            $entry['portfolio_value'] = $this->getPlayerPortfolioValue($player);
            $entry['funds_available'] = $this->getTransactionBalance($player);
            $entry['total'] = $entry['portfolio_value'] + $entry['funds_available'];
            $leaderboard[] = $entry;
        }

        return collect($leaderboard)->sortByDesc('total', SORT_NUMERIC);
    }

    public function getPlayerPortfolioValue(Player $player)
    {
        return $this->getPlayerPortfolio($player)->sum('value');
    }

    /**
     * Get the currently running game
     * @return Game|null
     */
    public function getCurrentGame()
    {
        return Game::where('finishes_at', '>', DB::raw('NOW()'))->first();
    }


    /**
     * @param Coin $coin
     * @param string $currency
     * @return null|string
     */
    public function getPriceOfCoin(Coin $coin, string $currency = 'USD')
    {
        /** @var CoinDataService $coinDataService */
        $coinDataService = resolve(CoinDataService::class);
        $price = $coinDataService->currentPrice(new PriceRequest($coin));

        return $price->getPrice($currency);
    }

    /**
     * @param Game $game
     * @param mixed $userPlatformId
     * @throws GameException
     */
    public function checkCanAddPlayer(Game $game, $userPlatformId)
    {
        if ($game->hasFinished()) {
            throw new GameException('The game has already finished, so no new players can join.');
        }

        if ($game->players()->where('platform_id', $userPlatformId)->exists()) {
            throw new GameException("You're already registered :ok:");
        }
    }

    /**
     * @param Game $game
     * @throws TradeException
     */
    public function checkCanAddTrade(Game $game)
    {
        if ($game->hasFinished()) {
            throw new TradeException('Cannot add trade, game has already finished.');
        }
        if (!$game->hasStarted()) {
            throw new TradeException('Trading has not started yet :hourglass: Starts in '.$game->starts_at->diffForHumans());
        }
    }

    /**
     * @param Game $game
     * @throws TradeException
     */
    public function checkCanAddTransaction(Game $game)
    {
        if ($game->hasFinished()) {
            throw new TradeException('Cannot add transaction, game has already finished');
        }
    }

    public function endCurrentGame(): Game
    {
        $game = $this->getCurrentGame();
        if ($game === null) {
            throw new GameException('There is no game running :thonk:');
        }

        $game->finishes_at = Carbon::now();
        $game->save();

        // TODO: anything else?

        return $game;
    }

}