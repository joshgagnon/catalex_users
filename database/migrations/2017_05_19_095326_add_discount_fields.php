<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('billing_details', function(Blueprint $table) {
            $table->string('discount_percent')->nullable()->default(null);
        });
        
        Schema::table('charge_logs', function(Blueprint $table) {
            $table->string('discount_percent')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('billing_details', function(Blueprint $table) {
            $table->dropColumn('discount_percent');
        });
    
        Schema::table('charge_logs', function(Blueprint $table) {
            $table->dropColumn('discount_percent');
        });
    }
}
