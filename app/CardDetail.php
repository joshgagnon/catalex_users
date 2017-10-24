<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CardDetail extends Model
{
    protected $fillable = ['card_token', 'expiry_date', 'masked_card_number'];

    public function billingDetails()
    {
        return $this->hasMany(BillingDetail::class);
    }
}
