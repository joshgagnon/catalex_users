<?php

use App\ChargeLog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentTypeToChargeLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('charge_logs', function (Blueprint $table) {
            $table->text('payment_type')->default(ChargeLog::PAYMENT_TYPE_DPS_CC);
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
            $table->dropColumn('payment_type');
        });
    }
}
