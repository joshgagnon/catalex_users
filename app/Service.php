<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'service_registrations')->withPivot('price_in_cents', 'period', 'access_level')->withTimestamps();
    }

    public function organisations()
    {
        return $this->belongsToMany(Organisation::class, 'service_registrations')->withPivot('price_in_cents', 'period', 'access_level')->withTimestamps();
    }

    public function billingItems()
    {
        return $this->hasMany(BillingItem::class);
    }
}
