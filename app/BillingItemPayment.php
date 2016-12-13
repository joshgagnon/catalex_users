<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BillingItem;
use App\ChangeLog;

class BillingItemPayment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['paid_until'];

    /**
     * The item this payment was made for
     */
    public function billingItem()
    {
        return $this->belongsTo(BillingItem::class);
    }

    /**
     * The charge log the payment appeared on
     */
    public function chargeLog()
    {
        return $this->belongsTo(ChangeLog::class);
    }
}
