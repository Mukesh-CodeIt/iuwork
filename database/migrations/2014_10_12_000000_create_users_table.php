<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->engine="InnoDB";
            $table->bigIncrements('id');
            $table->unsignedBigInteger('role_id')->default(2);
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade')->onUpdate('cascade');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('gender')->nullable();
            $table->string('image')->nullable();
            $table->enum('status',['activated', 'deactivated'])->default('activated');
            $table->enum('type',['employer', 'employee'])->nullable();
            $table->enum('visibility',['visible', 'invisible'])->default('visible');
            $table->boolean('is_deleted')->default(false);
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('geo_latitude')->nullable();
            $table->string('geo_longitude')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('post_zip_code')->nullable();
            $table->string('personal_utr_number')->nullable();
            $table->string('national_insaurance_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('business_name')->nullable();
            $table->string('line_manager_name')->nullable();
            $table->text('personal_details')->nullable();
            $table->datetime('date_of_birth')->nullable();
            $table->datetime('registration_date')->nullable();
            $table->bigInteger('last_updated_by')->nullable();
            $table->datetime('last_updated_date')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
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
      Schema::dropIfExists('users');
      // Schema::table('users', function (Blueprint $table) {
      //   $table->dropForeign('users_role_id_foreign');
      //   $table->dropColumn('role_id');
      // });
    }
}
