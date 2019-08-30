<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
          $table->engine="InnoDB";
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_from_id')->nullable();
            $table->foreign('user_from_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('user_to_id')->nullable();
            $table->foreign('user_to_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->text('message_text')->nullable();
            $table->datetime('message_date')->nullable();
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
        Schema::dropIfExists('messages');
    }
}
