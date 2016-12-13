<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReplaceServiceUserWithServiceRegistrations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('service_user');

        Schema::create('service_registrations', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('service_id')->unsigned();
            $table->foreign('service_id')->references('id')->on('services');

            $table->integer('user_id')->unsigned()->nullable()->default(NULL);
            $table->foreign('user_id')->references('id')->on('users');

            $table->integer('organisation_id')->unsigned()->nullable()->default(NULL);
            $table->foreign('organisation_id')->references('id')->on('organisations');

            $table->integer('price_in_cents')->nullable();
            $table->enum('period', ['monthly', 'annually'])->nullable();

            $table->enum('access_level', ['full_access', 'no_access'])->default('full_access');

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
        Schema::drop('service_registrations');

        // Re-create the service_user table
        Schema::create('service_user', function (Blueprint $table) {
            $table->integer('service_id')->unsigned();
            $table->foreign('service_id')->references('id')->on('services');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');

            $table->unique(['service_id', 'user_id']);

            $table->integer('price_in_cents');
            $table->enum('period', ['monthly', 'annually']);

            $table->timestamps();
        });
    }
}
