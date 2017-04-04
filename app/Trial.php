<?php

namespace App;

use App\Library\Billing;
use Carbon\Carbon;
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

    /**
     * Return the most recent trial for a billable entity for a service.
     * If trial for that entity for the service doesn't exist, create one starting today.
     *
     * @param $billableEntity
     * @param $service
     * @return static
     */
    protected static function findOrCreate($billableEntity, $service)
    {
        if (is_string($service)) {
            $service = Service::where('name', $service)->first();
        }

        $trial = $billableEntity->trials()->where('trials.service_id', $service->id)->orderBy('created_at', 'DESC')->first();

        if (!$trial) {
            $trial = Trial::create([
                $billableEntity->foreignIdName() => $billableEntity->id,
                'service_id' => $service->id,
                'start_date' => Carbon::today(),
                'days_in_trial' => Billing::DAYS_IN_TRIAL_PERIOD,
            ]);
        }

        return $trial;
    }
}
