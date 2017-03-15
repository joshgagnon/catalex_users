<?php

namespace App;

use Config;
use App\Models\Billable;
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

    public function members() {
        return $this->hasMany('App\User');
    }

    public function membersWithTrashed() {
        return $this->hasMany('App\User')->withTrashed();
    }

    public function billingExempt() {
        // TODO: Remove beta org code
        return $this->id === Config::get('constants.beta_organisation');
    }

    public function owedAmount() {
        // No pro-rating if never billed before
        if(!$this->everBilled()) return '0.00';

        // Pro-rate active but unpaid users
        $sum = '0.00';

        foreach($this->members as $member) {
            $sum = bcadd($sum, $member->prorate($this->paid_until), 2);
        }

        return $sum;
    }

    public function paymentAmount() {
        switch($this->billing_detail->period) {
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

    public function sendInvoices($type, $invoiceNumber, $listItems, $totalAmount, $gst,  $orgName=null, $orgId=null) {
        $orgId = 'CT' . str_pad((string)$this->id, 5, '0', STR_PAD_LEFT);
        foreach ($this->members as $member) {
            if ($member->can('edit_own_organisation')) {
                $member->sendInvoices($type, $invoiceNumber,  $listItems, $totalAmount, $gst,$this->name, $orgId);
            }
        }
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
}
