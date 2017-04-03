<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trial extends Model
{
    protected $fillable = ['user_id', 'organisation_id', 'service_id', 'start_date', 'days_in_trial'];

    protected $dates = ['start_date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
