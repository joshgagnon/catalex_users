<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organisation extends Model {

	use SoftDeletes;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'billing_detail_id'];

	public static $rules = [
		'name' => 'required|max:255',
	];

	public function billing_details() {
		return $this->belongsTo('App\BillingDetail');
	}
}
