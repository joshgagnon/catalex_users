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
	protected $fillable = ['period', 'address_id', 'card_detail_id', 'billing_day', 'discount_percent'];

	public function address() {
		return $this->belongsTo('App\Address');
	}

	public function cardDetail()
    {
        return $this->belongsTo(CardDetail::class);
    }

	public function users()
	{
		return $this->hasMany(User::class);
	}

	public function organisations()
	{
		return $this->hasMany(Organisation::class);
	}

    public function getDiscountPercent()
    {
        $discountPercent = $this->discount_percent;

        if ($discountPercent && is_numeric($discountPercent)) {
            $discountPercentIsSane = $discountPercent > 0 && $discountPercent <= 100;

            return $discountPercentIsSane ? $discountPercent : 0;
        }

        // No discount percent
        return null;
    }
}
