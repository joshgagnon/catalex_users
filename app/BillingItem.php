<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BillingItemPayment;
use App\User;
use \Carbon\Carbon;

class BillingItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['item_id', 'item_type', 'json_data', 'active'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'json_data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organisation()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeDueForPayment($query)
    {
        return $query->whereNotExists(function ($query) {
                    $query->select(\DB::raw(1))
                          ->from('billing_item_payments')
                          ->whereRaw('billing_item_payments.billing_item_id = billing_items.id')
                          ->where('paid_until', '>=', Carbon::tomorrow());
                });
    }

    /**
     * Payments made for this billing item
     */
    public function payments()
    {
        return $this->hasMany(BillingItemPayment::class);
    }
}
