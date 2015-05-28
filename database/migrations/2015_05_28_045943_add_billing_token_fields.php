<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBillingTokenFields extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('billing_details', function(Blueprint $table) {
			$table->string('dps_billing_token', 16)->default('xxxxxxxxxxxxxxxx');
			$table->string('expiry_date', 4)->default('0199');
			$table->dateTime('last_billed')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('billing_details', function(Blueprint $table) {
			$table->dropColumn('last_billed');
			$table->dropColumn('expiry_date');
			$table->dropColumn('dps_billing_token');
		});
	}
}
