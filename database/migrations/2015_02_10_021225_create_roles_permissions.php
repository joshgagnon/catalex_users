<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesPermissions extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('roles', function(Blueprint $table) {
			$table->increments('id')->unsigned();

			$table->string('name')->unique();

			$table->timestamps();
		});

		Schema::create('role_user', function(Blueprint $table) {
			$table->integer('user_id')->unsigned();
			$table->integer('role_id')->unsigned();

			$table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('role_id')->references('id')->on('roles');

			$table->primary(['user_id', 'role_id']);
		});

		Schema::create('permissions', function(Blueprint $table) {
			$table->increments('id')->unsigned();

			$table->string('name')->unique();
			$table->string('display_name');

			$table->timestamps();
		});

		Schema::create('permission_role', function(Blueprint $table) {
			$table->integer('role_id')->unsigned();
			$table->integer('permission_id')->unsigned();

			$table->foreign('role_id')->references('id')->on('roles');
			$table->foreign('permission_id')->references('id')->on('permissions');

			$table->primary(['role_id', 'permission_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('role_user', function(Blueprint $table) {
			$table->dropForeign('role_user_user_id_foreign');
			$table->dropForeign('role_user_role_id_foreign');
		});
		Schema::table('permission_role', function(Blueprint $table) {
			$table->dropForeign('permission_role_permission_id_foreign');
			$table->dropForeign('permission_role_role_id_foreign');
		});
		Schema::drop('permission_role');
		Schema::drop('permissions');
		Schema::drop('role_user');
		Schema::drop('roles');
	}
}
