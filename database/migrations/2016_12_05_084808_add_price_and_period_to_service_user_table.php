<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriceAndPeriodToServiceUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_user', function (Blueprint $table) {
            $table->integer('price_in_cents');
            $table->enum('period', ['monthly', 'annually']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_user', function (Blueprint $table) {
            $table->dropColumn('price_in_cents');
            $table->dropColumn('period');
        });
    }
}
