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
            $table->integer('rating_one')->default(0);
            $table->integer('rating_two')->default(0);
            $table->integer('rating_three')->default(0);
            $table->integer('rating_four')->default(0);
            $table->integer('rating_five')->default(0);
            $table->decimal('total_ratings', 8, 1)->default(0);
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
