<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayerTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_trades', function (Blueprint $table) {
            $table->increments('id');

            // Player the trade is made by
            $table->unsignedInteger('player_id');

            // Coin being traded
            $table->unsignedInteger('coin_id');

            // Amount (buys have positive amounts, sells have negative amounts)
            $table->decimal('amount', 20, 8);

            // Type of trade
            $table->enum('type_id', [
                \App\Models\Support\TradeType::BUY,
                \App\Models\Support\TradeType::SELL,
            ]);

            $table->text('description')->nullable();

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
        Schema::dropIfExists('player_trades');
    }
}
