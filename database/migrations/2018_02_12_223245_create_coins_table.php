<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * "Id": "4578",
    "Url": "/coins/nrs/overview",
    "ImageUrl": "/media/19834/nrs.png",
    "Name": "NRS",
    "Symbol": "NRS",
    "CoinName": "NoirShares",
    "FullName": "NoirShares (NRS)",
    "Algorithm": "Scrypt",
    "ProofType": "PoW/PoS",
    "FullyPremined": "0",
    "TotalCoinSupply": "5000000",
    "PreMinedValue": "N/A",
    "TotalCoinsFreeFloat": "N/A",
    "SortOrder": "174",
    "Sponsored": false,
    "IsTrading": true
     * @return void
     */
    public function up()
    {
        Schema::create('coins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('remote_id', 40);
            $table->string('name', 100);
            $table->string('full_name', 255);
            $table->string('symbol', 100);
            $table->string('image_url')->nullable();
            $table->string('info_url')->nullable();
            $table->string('algorithm', 100);
            $table->string('proof_type', 100);
            $table->unsignedBigInteger('total_supply')->nullable();
            $table->boolean('is_premined');
            $table->string('premined_value', 100);
            $table->string('total_free_float', 100);
            $table->boolean('is_trading');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique('remote_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coins');
    }
}
