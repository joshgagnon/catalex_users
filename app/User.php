<?php

namespace App;

use Config;
use App\Models\Billable;
use App\Models\ActiveUser;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use DB;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

    use Authenticatable, CanResetPassword, SoftDeletes, ActiveUser, Billable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password', 'organisation_id', 'billing_detail_id', 'free', 'is_shadow_user'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'paid_until'];

    protected static function boot()
    {
        parent::boot();

        // Delete first login tokens on boot
        static::deleting(function($user) {
            $user->firstLoginToken()->delete();
        });
    }

    public function organisation()
    {
        return $this->belongsTo('App\Organisation');
    }

    public function organisationInvites()
    {
        return $this->hasMany(OrganisationInvite::class, 'invited_user_id');
    }

    public function roles()
    {
        return $this->belongsToMany('App\Role');
    }

    public function meta()
    {
        return $this->hasOne(UserMeta::class);
    }

    public function emailVerificationToken()
    {
        return $this->hasOne(EmailVerificationToken::class);
    }

    public function fullName()
    {
        return $this->name;
    }

    public function isSubscribedTo($serviceId)
    {
        if ($this->organisation_id) {
            return $this->organisation->isSubscribedTo($serviceId);
        }

        if (!is_array($serviceId)) {
            $serviceId = [$serviceId];
        }

        return DB::table('service_registrations')->where('user_id', $this->id)->where('service_id', $serviceId)->exists();
    }

    public function billingExempt()
    {
        if ($this->free) {
            return true;
        }

        if ($this->organisation && $this->organisation->billingExempt()) {
            return true;
        }

        return $this->hasRole('global_admin');
    }

    public function paymentAmount() {
        if(!$this->billing_detail) return '0.00';

        switch($this->billing_detail->period) {
            case 'monthly':
                return Config::get('constants.monthly_price');
            case 'annually':
                return Config::get('constants.annual_price');
            default:
                throw new Exception('Billing period must be one of "monthly" or "annually"');
        }
    }

    public function accountNumber()
    {
        if ($this->organisation_id) {
            return $this->organisation()->accountNumber();
        }

        return 'CU' . str_pad((string)$this->id, 5, '0', STR_PAD_LEFT);
    }

    public function addRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }
        elseif (is_string($role)) {
            // Check named roles first to avoid attach collision
            if($this->hasRole($role)) return;
            $role = Role::where('name', '=', $role)->value('id');
        }
        $this->roles()->attach($role);
    }

    public function addRoles($roles)
    {
        foreach($roles as $role) {
            $this->addRole($role);
        }
    }

    public function removeRole($role)
    {
        if(is_object($role)) {
            $role = $role->getKey();
        }
        elseif(is_string($role)) {
            $role = Role::where('name', '=', $role)->value('id');
        }

        $this->roles()->detach($role);
    }

    public function removeRoles($roles)
    {
        foreach($roles as $role) {
            $this->removeRole($role);
        }
    }

    /**
     * Return whether the user has the named role.
     *
     * @param  string  $role
     * @return bool
     */
    public function hasRole($role)
    {
        foreach ($this->roles as $r) {
            if ($r->name === $role) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return whether the user has the requested permission via any of its roles.
     *
     * @param  string  $permission
     * @return bool
     */
    public function can($permission)
    {
        foreach($this->roles as $role) {
            foreach($role->permissions as $p) {
                if($p->name === $permission) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return whether this user and another belong to the same organisation.
     *
     * @param  User  $other
     * @return bool
     */
    public function sharesOrganisation($other)
    {
        return $this->organisation && $other->organisation && $this->organisation->id === $other->organisation->id;
    }

    /**
     * Override SoftDelete trait implementation to play nicely with ActiveUser trait.
     *
     * @return void
     */
    protected function runSoftDelete() {
        $query = $this->newQueryWithoutScopes()->where($this->getKeyName(), $this->getKey());

        $this->{$this->getDeletedAtColumn()} = $time = $this->freshTimestamp();

        $query->update(array($this->getDeletedAtColumn() => $this->fromDateTime($time)));
    }

    protected function getAllDueBillingItems($service)
    {
       # fwrite(STDOUT, print_r($this->id, TRUE));
       # fwrite(STDOUT, print_r($service->billingItems()->get(), TRUE));
        ##fwrite(STDOUT, print_r($service->billingItems()->where('user_id', '=', $this->id)->get(), TRUE));
        return $service->billingItems()
                       ->where('user_id', '=', $this->id)
                       ->dueForPayment()
                       ->get();
	}

	public function getBillableEntity()
	{
		return $this->organisation ? $this->organisation : $this;
	}

	public function billableType()
	{
		return 'user';
	}

	public function shouldBill()
	{
		return !$this->organisation_id
               && !$this->free
			   && $this->billing_detail_id
			   && $this->isBillingDay()
			   && $this->needsBilled();
	}

    public function accessLogs()
    {
        return $this->hasMany(AccessLog::class);
    }

    public function firstLoginToken()
    {
        return $this->hasOne(FirstLoginToken::class);
    }

    public function hasBillingSetup()
    {
        if ($this->organisation) {
            return $this->organisation->hasBillingSetup();
        }

        return $this->billing_detail()->exists();
    }

    public function syncSubscriptions($subscriptions)
    {
        $this->services()->sync($subscriptions);
        $this->syncSubscriptionsWithBillingItems();
    }

    public function syncSubscriptionsWithBillingItems()
    {
        // This array maps service names to item types for the billing item.
        // If a service is not in this array, then it wont be billed as a subscription (eg. Good Companies)
        $serviceTypeMappings = [
            Service::SERVICE_NAME_CATALEX_SIGN => BillingItem::ITEM_TYPE_SIGN_SUBSCRIPTION,
            Service::SERVICE_NAME_COURT_COSTS => BillingItem::ITEM_TYPE_COURT_COSTS_SUBSCRIPTION,
        ];

        $userServiceIds = $this->services()->where('is_paid_service', true)->get()->pluck('id')->toArray();
        $paidServices = Service::where('is_paid_service', true)->get();

        foreach ($paidServices as $service) {
            if (array_key_exists($service->name, $serviceTypeMappings)) {
                $isSubscribed = in_array($service->id, $userServiceIds);
                $billingItem = $this->billingItems()->where('service_id', $service->id)->first();

                if (!$billingItem && $isSubscribed) {
                    // Create billing item
                    BillingItem::forceCreate([
                        'user_id' => $this->id,
                        'service_id' => $service->id,
                        'item_id' => $this->id, // we need something here that is unique to the service id, user id is the easiest option
                        'item_type' => $serviceTypeMappings[$service->name],
                        'json_data' => json_encode(['user_name' => $this->name]),
                        'active' => true,
                    ]);
                }
                else if ($billingItem && $billingItem->active !== $isSubscribed) {
                    $billingItem->update(['active' => $isSubscribed]);
                }
            }
        }
    }
}
