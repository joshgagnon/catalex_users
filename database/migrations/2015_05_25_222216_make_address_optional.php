<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeAddressOptional extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('billing_details', function(Blueprint $table) {
			$table->integer('address_id')->unsigned()->nullable()->change();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('billing_details', function(Blueprint $table) {
			$table->integer('address_id')->unsigned()->change();
		});
	}
}
