<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingItemPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_item_payments', function (Blueprint $table) {
            $table->increments('id');

            $table->date('paid_until');

            $table->integer('billing_item_id')->unsigned();
            $table->foreign('billing_item_id')->references('id')->on('billing_items');

            $table->integer('charge_log_id')->unsigned();
            $table->foreign('charge_log_id')->references('id')->on('charge_logs');

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
        Schema::drop('billing_item_payments');
    }
}
