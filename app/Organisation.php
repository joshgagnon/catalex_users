<?php namespace App;

use App\Models\Billable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organisation extends Model {

	use SoftDeletes, Billable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'billing_detail_id', 'free'];

	public static $rules = [
		'name' => 'required|max:255',
	];

	public function members() {
		return $this->hasMany('App\User');
	}

	public function membersWithTrashed() {
		return $this->hasMany('App\User')->withTrashed();
	}

	protected function memberCount() {
		return count(array_filter($this->members->all(), function($member) {
			return $member->active;
		}));
	}

	protected function billingExempt() {
		// TODO: Remove beta org code
		return $this->free || $this->id === Config::get('constants.beta_organisation');
	}
}
