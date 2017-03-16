<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAmountToBillingItemPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('billing_item_payments', function (Blueprint $table) {
            $table->string('amount', 16)->nullable();
            $table->string('gst', 16)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('billing_item_payments', function (Blueprint $table) {
            $table->dropColumn(['amount', 'gst']);
        });
    }
}
