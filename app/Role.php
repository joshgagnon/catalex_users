<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model {

	public static $rules = [
		'name' => 'required|unique|between:3,128',
	];

    protected $fillable = ['name'];

	public function users() {
		return $this->belongsToMany('App\User');
	}

	public function permissions() {
		return $this->belongsToMany('App\Permission');
	}

	public function addPermission($permission) {
		if(is_object($permission)) {
			$permission = $permission->getKey();
		}
		elseif(is_string($permission)) {
			$permission = Permission::where('name', '=', $permission)->value('id');
		}

		$this->permissions()->attach($permission);
	}

	public function addPermissions($permissions) {
		foreach($permissions as $permission) {
			$this->addPermission($permission);
		}
	}

	public function removePermission($permission) {
		if(is_object($permission)) {
			$permission = $permission->getKey();
		}
		elseif(is_string($permission)) {
			$permission = Permission::where('name', '=', $permission)->value('id');
		}

		$this->permissions()->detach($permission);
	}

	public function removePermissions($permissions) {
		foreach($permissions as $permission) {
			$this->removePermission($permission);
		}
	}
}
