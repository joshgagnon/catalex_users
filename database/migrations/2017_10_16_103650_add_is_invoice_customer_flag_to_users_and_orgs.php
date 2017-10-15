<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsInvoiceCustomerFlagToUsersAndOrgs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users',  function (Blueprint $table) {
            $table->boolean('is_invoice_customer')->default(false);
        });

        Schema::table('organisations',  function (Blueprint $table) {
            $table->boolean('is_invoice_customer')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users',  function (Blueprint $table) {
            $table->dropColumn('is_invoice_customer');
        });

        Schema::table('organisations',  function (Blueprint $table) {
            $table->dropColumn('is_invoice_customer');
        });
    }
}
