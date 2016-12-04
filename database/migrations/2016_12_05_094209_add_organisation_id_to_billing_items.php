<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrganisationIdToBillingItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('billing_items', function (Blueprint $table) {
            $table->integer('organisation_id')->unsigned()->nullable()->default(NULL);
            $table->foreign('organisation_id')->references('id')->on('organisations');

            $table->integer('user_id')->unsigned()->nullable()->default(NULL)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('billing_items', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->nullable(false)->change();

            $table->dropForeign(['organisation_id']);
            $table->dropColumn('organisation_id');
        });
    }
}
