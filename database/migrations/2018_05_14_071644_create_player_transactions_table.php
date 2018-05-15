<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayerTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_transactions', function (Blueprint $table) {
            $table->increments('id');

            // Associated player
            $table->unsignedInteger('player_id');

            // Amount (deposits have positive amounts, withdraws have negative amounts)
            $table->decimal('amount');

            // Type of trade
            $table->enum('type_id', [
                \App\Models\Support\TransactionType::DEPOSIT,
                \App\Models\Support\TransactionType::WITHDRAW,
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
        Schema::dropIfExists('player_transactions');
    }
}
