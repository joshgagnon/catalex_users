<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_recipients', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('organisation_id')->unsigned();
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');

            $table->text('name');
            $table->text('email');

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
        Schema::drop('invoice_recipients');
    }
}
