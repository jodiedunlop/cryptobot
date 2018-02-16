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
            $table->string('symbol', 40);
            $table->string('image_url')->nullable();
            $table->string('info_url')->nullable();
            $table->string('algorithm', 100)->nullable();
            $table->string('proof_type', 100)->nullable();
            $table->unsignedBigInteger('total_supply')->default(0); // CMC
            $table->unsignedBigInteger('available_supply')->default(0); // CMC
            $table->unsignedBigInteger('max_supply')->default(0); // CMC
            $table->unsignedBigInteger('volume_usd_24h')->default(0); // CMC
            $table->unsignedBigInteger('market_cap_usd')->default(0); // CMC
            $table->decimal('percent_change_1h')->default(0); // CMC
            $table->decimal('percent_change_24h')->default(0); // CMC
            $table->decimal('percent_change_7d')->default(0); // CMC
            $table->boolean('is_premined')->nullable();
            $table->string('premined_value', 100)->nullable();
            $table->string('total_free_float', 100)->nullable();
            $table->boolean('is_trading')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('rank')->default(0);
            $table->timestamp('sourced_at')->nullable();
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
