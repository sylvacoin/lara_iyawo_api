<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender');
            $table->integer('user_group_id');
            $table->string('phone')->unique();
            $table->string('email')->unique();
            $table->integer('handler_id')->nullable();
            $table->string('customer_no')->nullable();
            $table->double('balance')->default(0);
            $table->double('w_balance')->default(0);
            $table->double('p_balance')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('has_alert')->default(false);
            $table->string('password');
            $table->boolean('is_flagged')->default(false);
            $table->rememberToken();
            $table->timestamps();

            $table->index('handler_id');
            $table->index('customer_no');
            $table->index('email');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
