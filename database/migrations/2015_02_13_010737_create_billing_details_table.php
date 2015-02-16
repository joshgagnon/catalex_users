<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('billing_details', function(Blueprint $table) {
			$table->increments('id');

			$table->enum('period', ['monthly', 'annually']);

			$table->timestamps();

			$table->softDeletes();
		});

		Schema::table('users', function(Blueprint $table) {
			$table->integer('billing_detail_id')->unsigned()->nullable();
			$table->foreign('billing_detail_id')->references('id')->on('billing_details');
		});

		Schema::table('organisations', function(Blueprint $table) {
			$table->integer('billing_detail_id')->unsigned();
			$table->foreign('billing_detail_id')->references('id')->on('billing_details');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function($table) {
			$table->dropForeign('users_billing_detail_id_foreign');
			$table->dropColumn('billing_detail_id');
		});

		Schema::table('organisations', function($table) {
			$table->dropForeign('organisations_billing_detail_id_foreign');
			$table->dropColumn('billing_detail_id');
		});

		Schema::drop('billing_details');
	}

}
