<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemovePeriodFromServiceRegistrations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_registrations', function (Blueprint $table) {
            $table->dropColumn('period');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_registrations', function (Blueprint $table) {
            $table->enum('period', ['monthly', 'annually'])->default('annually');
        });
    }
}
