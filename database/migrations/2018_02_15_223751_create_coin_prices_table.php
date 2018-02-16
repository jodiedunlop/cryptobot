<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoinPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('coin_id');
            $table->unsignedInteger('market_id')->default(0);
            $table->string('currency_symbol', 40); // Currency pair
            $table->decimal('price', 24, 8)->nullable();
            $table->timestamp('sourced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coin_prices');
    }
}
