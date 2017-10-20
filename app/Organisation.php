<?php

namespace App;

use App\Models\Billable;
use Config;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organisation extends Model
{
    use SoftDeletes, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'billing_detail_id'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'paid_until'];

    public static $rules = [
        'name' => 'required|max:255',
    ];

    protected static function boot()
    {
        parent::boot();

        // Delete first login tokens when deleting
        static::deleting(function ($org) {
            $org->userInvites()->delete();
        });
    }

    public function members()
    {
        return $this->hasMany('App\User');
    }

    public function membersWithTrashed()
    {
        return $this->hasMany('App\User')->withTrashed();
    }

    public function invoiceRecipients()
    {
        return $this->hasMany(InvoiceRecipient::class);
    }

    public function billingExempt()
    {
        // TODO: Remove beta org code
        return $this->id === Config::get('constants.beta_organisation');
    }

    public function userInvites()
    {
        return $this->hasMany(OrganisationInvite::class);
    }

    public function isSubscribedTo($serviceId)
    {
        if (!is_array($serviceId)) {
            $serviceId = [$serviceId];
        }

        $memberIds = $this->members()->get()->pluck('id')->toArray();
        return DB::table('service_registrations')->whereIn('user_id', $memberIds)->whereIn('service_id', $serviceId)->exists();
    }

    public function paymentAmount()
    {
        switch ($this->billing_detail->period) {
            case 'monthly':
                $periodCost = Config::get('constants.monthly_price');
                break;
            case 'annually':
                $periodCost = Config::get('constants.annual_price');
                break;
            default:
                throw new Exception('Billing period must be one of "monthly" or "annually"');
        }

        return bcmul($periodCost, (string)$this->members->count(), 2);
    }

    public function accountNumber()
    {
        return 'CT' . str_pad((string)$this->id, 5, '0', STR_PAD_LEFT);
    }

    public function getAllDueBillingItems($service)
    {
        // Get due billing items for this organisation
        $billingItems = $service->billingItems()
            ->where('organisation_id', '=', $this->id)
            ->dueForPayment()
            ->get();

        // Get  due billing items for members of this organisation
        $members = $this->members()->get();

        if ($members->count() > 0) {
            $userIds = $members->pluck('id')->toArray();
            $membersBillingItems = $service->billingItems()
                ->whereIn('user_id', $userIds)
                ->dueForPayment()
                ->get();

            $billingItems = $billingItems->merge($membersBillingItems);
        }

        // Return all due billing item for both this organisation and it's members
        return $billingItems;
    }

    public function billableType()
    {
        return 'organisation';
    }

    public function shouldBill()
    {
        return $this->isBillingDay()
            && $this->billing_detail_id
            && $this->needsBilled();
    }

    public function hasBillingSetup()
    {
        return $this->billing_detail()->exists();
    }

    /**
     * Get a list of all users who should be sent an invoice
     *
     * @return mixed
     */
    public function invoiceableUsers()
    {
        $invoiceableUsers = [];

        foreach ($this->members as $member) {
            if ($member->can('edit_own_organisation')) {
                $invoiceableUsers[] = [
                    'name'  => $member->name,
                    'email' => $member->email,
                ];
            }
        }

        return $invoiceableUsers;
    }

    /**
     * Join this organisation.
     *
     * @param \App\User $user
     */
    public function join(User $user)
    {
        $user->update(['organisation_id' => $this->id]);

        // When a use joins an org, automatically give them GC access if anyone else in the org has GC access.
        // Do not automatically give them sign.

        $gcService = Service::where('name', 'Good Companies')->first();

        $servicesToSync = $this->isSubscribedTo($gcService->id) ? [$gcService->id] : [];
        $user->syncSubscriptions($servicesToSync);
    }

    /**
     * Leave this organisation
     *
     * @param \App\User $user
     */
    public function leave(User $user)
    {
        $user->update(['organisation_id' => null]);

        // When a user leaves an org, remove all subscribed services.
        $user->syncSubscriptions([]);
    }
}
