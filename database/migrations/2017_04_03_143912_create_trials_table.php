<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trials', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')->unsigned()->nullable()->default(NULL);
            $table->foreign('user_id')->references('id')->on('users');

            $table->integer('organisation_id')->unsigned()->nullable()->default(NULL);
            $table->foreign('organisation_id')->references('id')->on('organisations');

            $table->integer('service_id');
            $table->foreign('service_id')->references('id')->on('services');

            $table->date('start_date');
            $table->integer('days_in_trial');

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
        Schema::drop('trials');
    }
}
