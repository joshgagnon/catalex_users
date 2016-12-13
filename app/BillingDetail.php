<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillingDetail extends Model {

	use SoftDeletes;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['period', 'address_id', 'dps_billing_token', 'expiry_date', 'billing_day'];

	public function address() {
		return $this->belongsTo('App\Address');
	}

	public function users()
	{
		return $this->hasMany(User::class);
	}

	public function organisations()
	{
		return $this->hasMany(Organisation::class);
	}
}
