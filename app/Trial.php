<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trial extends Model
{
    protected $fillable = ['user_id', 'organisation_id', 'service_id', 'start_date', 'days_in_trial'];

    protected $dates = ['start_date'];

    protected $appends = ['end_date'];

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

    protected function getEndDateAttribute()
    {
        return $this->start_date->copy()->addDays($this->days_in_trial);
    }
}
