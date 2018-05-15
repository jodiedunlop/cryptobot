<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->increments('id');

            // Slack channel where game should broadcast to
            $table->string('platform_id');

            // Starting balance (USD)
            $table->decimal('start_balance');


            // The player that started the game
            $table->unsignedInteger('started_by_player_id')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // When the game starts (eg. an offset to allow players to register)
            $table->dateTime('starts_at');

            // When the game finishes
            $table->dateTime('finishes_at');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('games');
    }
}
