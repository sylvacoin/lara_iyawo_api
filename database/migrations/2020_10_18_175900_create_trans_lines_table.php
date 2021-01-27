<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trans_lines', function (Blueprint $table) {
            $table->id();
            $table->integer('trans_header_id');
            $table->integer('card_id');
            $table->double('amount');
            $table->timestamps();

            $table->index('trans_header_id');
            $table->index('card_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trans_lines');
    }
}
