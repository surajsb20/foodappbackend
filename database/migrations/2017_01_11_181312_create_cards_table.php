<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->enum('card_type', ['stripe', 'braintree', 'bambora'])->default('stripe');;
            $table->string('last_four');
            $table->string('card_id');
            $table->string('exp_year')->nullable();
            $table->string('exp_month')->nullable();
            $table->string('brand')->nullable();
            $table->string('cvc')->nullable();
            $table->integer('is_default')->default(0);
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
        Schema::dropIfExists('cards');
    }
}
