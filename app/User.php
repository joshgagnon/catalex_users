<?php namespace App;

use App\Models\ActiveUser;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword, SoftDeletes, ActiveUser;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['first_name', 'last_name', 'email', 'password', 'organisation_id', 'billing_detail_id'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

	public function billing_details() {
		return $this->belongsTo('App\BillingDetail');
	}

	public function organisation() {
		return $this->belongsTo('App\Organisation');
	}

	public function roles() {
		return $this->belongsToMany('\App\Role');
	}

	public function fullName() {
		return $this->first_name . ' ' . $this->last_name;
	}

	public function addRole($role) {
		if(is_object($role)) {
			$role = $role->getKey();
		}
		elseif(is_string($role)) {
			$role = Role::where('name', '=', $role)->pluck('id');
		}

		$this->roles()->attach($role);
	}

	public function addRoles($roles) {
		foreach($roles as $role) {
			$this->addRole($role);
		}
	}

	public function removeRole($role) {
		if(is_object($role)) {
			$role = $role->getKey();
		}
		elseif(is_string($role)) {
			$role = Role::where('name', '=', $role)->pluck('id');
		}

		$this->roles()->detach($role);
	}

	public function removeRoles($roles) {
		foreach($roles as $role) {
			$this->removeRole($role);
		}
	}

	/**
	 * Return whether the user has the named role.
	 *
	 * @param  string  $role
	 * @return bool
	 */
	public function hasRole($role) {
		foreach($this->roles as $r) {
			if($r->name === $role) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Return whether the user has the requested permission via any of its roles.
	 *
	 * @param  string  $permission
	 * @return bool
	 */
	public function can($permission) {
		foreach($this->roles as $role) {
			foreach($role->permissions as $p) {
				if($p->name === $permission) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Return whether this user and another belong to the same organisation.
	 *
	 * @param  App\User  $other
	 * @return bool
	 */
	public function sharesOrganisation($other) {
		return $this->organisation && $other->organisation && $this->organisation->id === $other->organisation->id;
	}

	/**
	 * Override SoftDelete trait implementation to play nicely with ActiveUser trait.
	 *
	 * @return void
	 */
	protected function runSoftDelete() {
		$query = $this->newQueryWithoutScopes()->where($this->getKeyName(), $this->getKey());

		$this->{$this->getDeletedAtColumn()} = $time = $this->freshTimestamp();

		$query->update(array($this->getDeletedAtColumn() => $this->fromDateTime($time)));
	}

	/**
	 * Make this user active.
	 *
	 * @return void
	 */
	public function activate() {
		$this->active = true;

		$this->save();
	}

	/**
	 * Make this user inactive.
	 *
	 * @return void
	 */
	public function deactivate() {
		$this->active = false;

		$this->save();
	}
}
