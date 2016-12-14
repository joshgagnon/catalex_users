<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoveFreeAccountsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('free')->default(false);
        });

        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn('free');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('free');
        });

        Schema::table('organisations', function (Blueprint $table) {
            $table->boolean('free')->default(false);
        });
    }
}
