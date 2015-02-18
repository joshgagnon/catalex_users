<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('addresses', function(Blueprint $table) {
			$table->increments('id');

			$table->string('line_1')->nullable();
			$table->string('line_2')->nullable();
			$table->string('city');
			$table->string('state')->nullable();
			$table->string('iso3166_country', 2);

			$table->timestamps();

			$table->softDeletes();
		});

		Schema::table('billing_details', function(Blueprint $table) {
			$table->integer('address_id')->unsigned();
			$table->foreign('address_id')->references('id')->on('addresses');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('billing_details', function($table) {
			$table->dropForeign('billing_details_address_id_foreign');
			$table->dropColumn('address_id');
		});

		Schema::drop('addresses');
	}

}
