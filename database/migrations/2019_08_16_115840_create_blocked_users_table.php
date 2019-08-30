<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlockedUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blocked_users', function (Blueprint $table) {
          $table->engine="InnoDB";
          $table->bigIncrements('id');
          $table->unsignedBigInteger('blocked_user_id')->nullable();
          $table->foreign('blocked_user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
          $table->unsignedBigInteger('block_by_user_id')->nullable();
          $table->foreign('block_by_user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
          $table->datetime('blocked_date')->nullable();
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
        Schema::dropIfExists('blocked_users');
    }
}
