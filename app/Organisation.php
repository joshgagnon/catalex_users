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
	protected $fillable = ['name', 'billing_detail_id'];

	public static $rules = [
		'name' => 'required|max:255',
	];

	public function members() {
		return $this->hasMany('App\User');
	}
}
