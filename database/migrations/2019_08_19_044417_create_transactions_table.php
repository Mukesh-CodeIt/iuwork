<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->engine="InnoDB";
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_from_id')->nullable();
            $table->foreign('user_from_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('user_to_id')->nullable();
            $table->foreign('user_to_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('amount')->default(0);
            $table->bigInteger('balance')->default(0);
            $table->enum('transaction_status',['pending', 'completed', 'decline'])->default('pending');
            $table->enum('transaction_type',['deposited', 'purchased', 'released', 'withdrawn', 'refund'])->nullable();
            $table->datetime('transaction_date')->nullable();
            $table->bigInteger('last_updated_by')->nullable();
            $table->datetime('last_updated_date')->nullable();
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
        Schema::dropIfExists('transactions');
    }
}
