<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProRatedBilling extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('users', function(Blueprint $table) {
			$table->date('paid_until')->nullable()->default(null);
		});

		Schema::table('organisations', function(Blueprint $table) {
			$table->date('paid_until')->nullable()->default(null);
		});

		Schema::table('billing_details', function(Blueprint $table) {
			$table->dropColumn('last_billed');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('users', function(Blueprint $table) {
			$table->dropColumn('paid_until');
		});

		Schema::table('organisations', function(Blueprint $table) {
			$table->dropColumn('paid_until');
		});

		Schema::table('billing_details', function(Blueprint $table) {
			$table->dateTime('last_billed')->nullable();
		});
	}
}
