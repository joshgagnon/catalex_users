<?php

use Illuminate\Database\Seeder;

use App\Role;
use App\Permission;

class RolePermissionSeeder extends Seeder {

	/**
	 * Add all available roles and permissions.
	 *
	 * @return void
	 */
	public function run() {
		$role = [];
		$permission = [];

		$permission['edit_any_user'] = Permission::create([
			'name' => 'edit_any_user',
			'display_name' => 'edit all users',
		]);
		$permission['edit_organisation_user'] = Permission::create([
			'name' => 'edit_organisation_user',
			'display_name' => 'edit users in your organisation',
		]);
		$permission['edit_own_user'] = Permission::create([
			'name' => 'edit_own_user',
			'display_name' => 'edit your own profile',
		]);

		$permission['view_any_user'] = Permission::create([
			'name' => 'view_any_user',
			'display_name' => 'view all users',
		]);
		$permission['view_organisation_user'] = Permission::create([
			'name' => 'view_organisation_user',
			'display_name' => 'view users in your organisation',
		]);
		$permission['view_own_user'] = Permission::create([
			'name' => 'view_own_user',
			'display_name' => 'view your own profile',
		]);

		$permission['edit_any_organisation'] = Permission::create([
			'name' => 'edit_any_organisation',
			'display_name' => 'edit all organisations',
		]);
		$permission['edit_own_organisation'] = Permission::create([
			'name' => 'edit_own_organisation',
			'display_name' => 'edit your organisation',
		]);

		$permission['view_any_organisation'] = Permission::create([
			'name' => 'view_any_organisation',
			'display_name' => 'view all organisations',
		]);
		$permission['view_own_organisation'] = Permission::create([
			'name' => 'view_own_organisation',
			'display_name' => 'view your organisation',
		]);

		$role['global_admin'] = Role::create(['name' => 'global_admin']);
		$role['global_admin']->addPermissions($permission);

		$role['organisation_admin'] = Role::create(['name' => 'organisation_admin']);
		$role['organisation_admin']->addPermissions([
			$permission['edit_organisation_user'],
			$permission['view_organisation_user'],
			$permission['edit_own_organisation'],
			$permission['view_own_organisation'],
		]);

		$role['registered_user'] = Role::create(['name' => 'registered_user']);
		$role['registered_user']->addPermissions([
			$permission['edit_own_user'],
			$permission['view_organisation_user'],
			$permission['view_own_user'],
			$permission['view_own_organisation'],
		]);
	}
}
