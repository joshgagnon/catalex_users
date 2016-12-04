<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BillingItemPayment;

class ChargeLog extends Model {

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'timestamp';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['success', 'pending', 'user_id', 'organisation_id', 'total_amount', 'gst'];

	/**
	 * Don't use updated_at timestamp for this model.
	 *
	 * @param  mixed  $value
	 * @return void
	 */
	public function setUpdatedAt($value) {
		// Nothing to do
	}

	public function user() {
		return $this->belongsTo('App\User');
	}

	public function organisation() {
		return $this->belongsTo('App\Organisation');
	}

	public function billingItemPayments()
	{
		return $this->hasMany(BillingItemPayment::class);
	}
}
