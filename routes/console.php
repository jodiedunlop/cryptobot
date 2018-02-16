<?php

use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');


Artisan::command('coins:update', function () {
    $service = new \App\Services\CoinDataService();
    $numCoinsUpdated = $service->updateCoinList();
    $this->comment = "Updated $numCoinsUpdated coins";
})->describe('Fetch and update the coins list');

Artisan::command('coins:update-prices', function () {
    $service = new \App\Services\CoinDataService();
    $numCoinsUpdated = $service->updateCoinPrices();
    $this->comment = "Updated $numCoinsUpdated coins";
})->describe('Fetch and update the coins list');