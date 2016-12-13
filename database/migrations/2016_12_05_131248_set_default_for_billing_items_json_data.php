<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetDefaultForBillingItemsJsonData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('billing_items', function (Blueprint $table) {
            $table->string('json_data')->default('{}')->change();
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
            // Can't remove a default with using SQL (which locks us into one DBMS)
            // so we'll just set the default to an empty string
            $table->string('json_data')->default('')->change();
        });
    }
}
