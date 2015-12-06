<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChargeLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('charge_logs', function(Blueprint $table) {
			$table->increments('id');

			$table->boolean('success');

			$table->integer('user_id')->unsigned()->nullable()->default(NULL);
			$table->foreign('user_id')->references('id')->on('users');

			$table->integer('organisation_id')->unsigned()->nullable()->default(NULL);
			$table->foreign('organisation_id')->references('id')->on('organisations');

			$table->string('total_amount', 16);
			$table->string('gst', 16);

			$table->timestamp('timestamp');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('charge_logs');
	}
}
