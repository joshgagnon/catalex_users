<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationInvitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organisation_invites', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('invited_user_id')->unsigned();
            $table->foreign('invited_user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('inviting_user_id')->unsigned();
            $table->foreign('inviting_user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('organisation_id')->unsigned();
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');

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
        Schema::drop('organisation_invites');
    }
}
