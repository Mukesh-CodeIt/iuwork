<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->engine="InnoDB";
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_from_id')->nullable();
            $table->foreign('user_from_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('user_to_id')->nullable();
            $table->foreign('user_to_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->text('feedback_text')->nullable();
            $table->integer('rating_one')->nullable();
            $table->integer('rating_two')->nullable();
            $table->integer('rating_three')->nullable();
            $table->integer('rating_four')->nullable();
            $table->integer('rating_five')->nullable();
            $table->integer('total_ratings')->nullable();
            $table->datetime('rating_date')->nullable();
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
        Schema::dropIfExists('feedbacks');
    }
}
