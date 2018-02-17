<?php

//use App\Conversations\ExampleConversation;

$botman = resolve('botman');

$botman->hears('price ([\w]+)(?:\s+(.+))?', \App\Commands\PriceCommand::class);
$botman->hears('rank(?:\s+(\d+))?', \App\Commands\RankCommand::class);
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
