<?php

//use App\Conversations\ExampleConversation;

$botman = resolve('botman');

$botman->hears('price ([\w]+)(?:\s+(.+))?', \App\Commands\PriceCommand::class);

// Rank
$botman->hears('(rank|top)(?:\s+(\d+))?', \App\Commands\RankCommand::class);
$botman->hears('top(?:\s+(\d+))?', \App\Commands\RankCommand::class);

// Gainers
$botman->hears('(gainers|winners)(?:\s+(1h|24h|7d))?', \App\Commands\GainersCommand::class);
$botman->hears('(gainers|winners) image(?:\s+(1h|24h|7d))?', \App\Commands\GainersImageCommand::class);

// Losers
$botman->hears('losers(?:\s+(1h|24h|7d))?', \App\Commands\LosersCommand::class);


$botman->hears('Hi', function ($bot) {
    $bot->reply('Hello!');
});
//$botman->hears('Start conversation', function($bot) {
//    \Illuminate\Support\Facades\Log::info('Bot class: '.get_class($bot));
//    $bot->startConversation(new ExampleConversation());
//});

//$botman->fallback(function($bot) {
//    $bot->reply("Sorry, I'm not that smart.");
//});
