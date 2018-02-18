<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::match(['get', 'post'], '/botman', 'BotManController@handle');
Route::get('/botman/tinker', 'BotManController@tinker');


Route::get('/coins', 'CoinController@index');
Route::get('/coins/gainers', 'CoinController@gainers');
Route::get('/coin-images', 'CoinImageController@index');
Route::get('/coin-images/gainers', 'CoinImageController@gainers');
