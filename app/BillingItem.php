<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BillingItemPayment;
use App\User;
use \Carbon\Carbon;

class BillingItem extends Model
{
    const ITEM_TYPE_GC_COMPANY = 'gc_company';

    private $itemTypes = [
        self::ITEM_TYPE_GC_COMPANY
    ];

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
        // Check it hasn't been paid past tomorrow
        $query->whereNotExists(function ($query) {
                    $query->select(\DB::raw(1))
                          ->from('billing_item_payments')
                          ->whereRaw('billing_item_payments.billing_item_id = billing_items.id')
                          ->where('paid_until', '>=', Carbon::tomorrow())
                          ->where('active', true);
                });

        // Check it is active
        $query->where('billing_items.active', true);

        return $query;
    }

    public function scopeItemType($query, string $type)
    {
        if (!in_array($type, $this->itemTypes)) {
            throw new Exception('Unknown item type ' . $type);
        }

        return $query->where('item_type', '=', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('billing_items.active', true);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public static function forTypeAndId(string $type, int $itemId)
    {
        return self::itemType($type)->where('item_id', '=', $itemId)->first();
    }

    /**
     * Payments made for this billing item
     */
    public function payments()
    {
        return $this->hasMany(BillingItemPayment::class);
    }
}
