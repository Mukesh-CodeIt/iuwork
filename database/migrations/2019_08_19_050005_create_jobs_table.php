<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->engine="InnoDB";
            $table->bigIncrements('id');
            $table->string('job_title')->nullable();
            $table->enum('job_status',['pending', 'in-progress', 'completed'])->default('pending');
            $table->string('pay_rate_per_hour')->nullable();
            $table->datetime('job_start_time')->nullable();
            $table->datetime('job_end_time')->nullable();
            $table->datetime('job_posted_date')->nullable();
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
        Schema::dropIfExists('jobs');
    }
}
