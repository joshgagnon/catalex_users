<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name', 'is_paid_service'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'service_registrations')->withTimestamps();
    }

    public function organisations()
    {
        return $this->belongsToMany(Organisation::class, 'service_registrations')->withTimestamps();
    }

    public function billingItems()
    {
        return $this->hasMany(BillingItem::class);
    }
}
