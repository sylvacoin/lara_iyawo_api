<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransHeadersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trans_headers', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->index('customer_id');
            $table->string('trans_type')->index('trans_type'); //001 || 002
            $table->integer('card_id')->nullable()->index('card_id');
            $table->double('amount')->default(0);
            $table->integer('no_days')->nullable();
            $table->integer('trans_by')->index('trans_by');
            $table->string('trans_status')->index('trans_status');
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
        Schema::dropIfExists('trans_headers');
    }
}
