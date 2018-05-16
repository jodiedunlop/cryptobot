<?php

$botman = resolve('botman');

$botman->hears('price ([\w]+)(?:\s+(.+))?', \App\Commands\PriceCommand::class);
$botman->hears('(rank|top)(?:\s+(\d+))?', \App\Commands\RankCommand::class);
$botman->hears('top(?:\s+(\d+))?', \App\Commands\RankCommand::class);
$botman->hears('(?:gainers|winners)(?:\s+(1h|24h|7d))?', \App\Commands\GainersCommand::class);
$botman->hears('(?:gainers|winners) image(?:\s+(1h|24h|7d))?', \App\Commands\GainersImageCommand::class);
$botman->hears('losers(?:\s+(1h|24h|7d))?', \App\Commands\LosersCommand::class);
$botman->hears('Hi', function ($bot) {
    $bot->reply('Hello!');
});


// Game
$botman->hears('(?:start|new|begin) game', \App\Commands\Game\NewGameCommand::class);
$botman->hears('(?:stop|end|finish) game', \App\Commands\Game\EndGameCommand::class);
$botman->hears('(?:join game|register|play)', \App\Commands\Game\JoinGameCommand::class);
$botman->hears('(?:help|commands)', \App\Commands\Game\HelpCommand::class);
$botman->hears('(?:buy|purchase) (max|all|[\d\.]+) (\w+)', \App\Commands\Game\BuyCoinCommand::class);
$botman->hears('sell (max|all|[\d\.]+) (\w+)', \App\Commands\Game\SellCoinCommand::class);
$botman->hears('(?:funds|balance)', \App\Commands\Game\FundsCommand::class);
$botman->hears('portfolio', \App\Commands\Game\PortfolioCommand::class);
$botman->hears('trades', \App\Commands\Game\TradesCommand::class);
$botman->hears('game(?:\s*status)?', \App\Commands\Game\GameStatusCommand::class);
$botman->hears('leaderboard', \App\Commands\Game\LeaderboardCommand::class);