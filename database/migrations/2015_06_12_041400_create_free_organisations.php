<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFreeOrganisations extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('organisations', function(Blueprint $table) {
			$table->integer('billing_detail_id')->unsigned()->nullable()->change();

			$table->boolean('free')->default(false);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('organisations', function(Blueprint $table) {
			$table->dropColumn('free');
		});
	}
}
