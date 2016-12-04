<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name'];

    /**
     * Users that are registered for this service
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
