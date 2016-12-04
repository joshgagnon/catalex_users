<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeTotalAmountAndGstNullableOnChargeLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('charge_logs', function (Blueprint $table) {
            $table->string('total_amount', 16)->nullable()->change();
            $table->string('gst', 16)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('charge_logs', function (Blueprint $table) {
            $table->string('total_amount', 16)->nullable(false)->change();
            $table->string('gst', 16)->nullable(false)->change();
        });
    }
}
