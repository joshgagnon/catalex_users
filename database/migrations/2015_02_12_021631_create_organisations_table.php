<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('organisations', function(Blueprint $table) {
			$table->increments('id');

			$table->string('name');

			$table->timestamps();

			$table->softDeletes();
		});

		Schema::table('users', function(Blueprint $table) {
			$table->integer('organisation_id')->unsigned()->nullable();
			$table->foreign('organisation_id')->references('id')->on('organisations');
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
			$table->dropForeign('users_organisation_id_foreign');
			$table->dropColumn('organisation_id');
		});

		Schema::drop('organisations');
	}

}
