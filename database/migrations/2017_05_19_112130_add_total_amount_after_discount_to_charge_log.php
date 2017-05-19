<?php

use App\ChargeLog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTotalAmountAfterDiscountToChargeLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('charge_logs', function(Blueprint $table) {
            $table->string('total_before_discount', 16)->nullable()->default(null);
        });
        
        $existingChargeLogs = ChargeLog::get();
        
        foreach ($existingChargeLogs as $chargeLog) {
            $chargeLog->update(['total_before_discount' => $chargeLog->total_amount]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('charge_logs', function(Blueprint $table) {
            $table->dropColumn('total_before_discount');
        });
    }
}
