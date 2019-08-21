<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FreeOrgs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('skip_billing')->default(false);
        });
          Schema::table('organisations', function (Blueprint $table) {
            $table->boolean('skip_billing')->default(false);
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
            $table->removeColumn('skip_billing');
        });
        Schema::table('organisations', function (Blueprint $table) {
            $table->removeColumn('skip_billing');
        });


    }
}
