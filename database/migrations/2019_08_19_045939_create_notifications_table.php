<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->engine="InnoDB";
            $table->bigIncrements('id');
            $table->unsignedBigInteger('notify_user_id')->nullable();
            $table->foreign('notify_user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->text('data')->nullable();
            $table->enum('notify_status',['delivered', 'undelivered', 'read'])->nullable();
            $table->string('notify_type')->nullable();
            $table->string('notify_action')->nullable();
            $table->datetime('read_datetime')->nullable();
            $table->datetime('notify_date')->nullable();
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
        Schema::dropIfExists('notifications');
    }
}
