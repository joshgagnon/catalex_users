<?php namespace App\Models;

use Log;
use Config;
use App\ChargeLog;
use Carbon\Carbon;
use App\Library\Mail;
use App\Library\PXPay;
use GuzzleHttp\Client;
use App\Library\Billing;
use App\Service;
use App\BillingItem;
use App\BillingItemPayment;
use App\BillingDetail;

trait Billable {

    public function billing_detail()
    {
        return $this->belongsTo(BillingDetail::class);
    }

    /**
     * Services this user or organisation is registered to
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_registrations')->withPivot('price_in_cents', 'access_level')->withTimestamps();
    }

    /**
     * Billing items that belong to this user
     */
    public function billingItems()
    {
        return $this->hasMany(BillingItem::class);
    }

    abstract public function billingExempt();

    abstract public function paymentAmount();

    abstract public function sendInvoices($type, $invoiceNumber, $totalAmount, $gst, $listItem, $orgName=null, $orgId=null);

    abstract protected function getAllDueBillingItems($service);

    public function inTrial()
    {
        $organisation = $this->organisation;

        if ($organisation) {
            return $organisation->inTrial();
        }

        return $this->created_at->diffInMinutes(Carbon::now()) < Config::get('constants.trial_length_minutes');
    }

    public function isPaid()
    {
        $organisation = $this->organisation;

        if ($organisation) {
            return $organisation->isPaid();
        }

        if (!$this->paid_until) {
            return false;
        }

        return Carbon::now()->lt($this->paid_until->hour(23)->minute(59));
    }

    public function hasBrowserAccess()
    {
        return true;
    }

    public function hasSignAccess()
    {
        return true;
    }

    public function hasGoodCompaniesAccess()
    {
        return $this->hasAccess(Service::where('name', 'Good Companies')->first());
    }

    public function hasAccess(Service $service)
    {
        $billablesService = $this->services()->where('service_id', $service->id)->first();

        // If this billable entity is registered for this service, check their service level
        if ($billablesService != null) {
            return $billablesService->pivot->access_level == 'full_access';
        }

        // If this billable entity has an organisation, fallback to the organisation's access level
        if ($this->organisation) {
            return $this->organisation->hasAccess($service);
        }

        // If not registered for the service and not belonging to an organisation: this billable entity has no access
        return false;
    }

    public function everBilled()
    {
        $organisation = $this->organisation;

        if ($organisation) {
            return $organisation->everBilled();
        }

        return $this->billing_detail && $this->paid_until !== null;
    }

    public function setBillingPeriod($period)
    {
        $organisation = $this->organisation;

        if ($organisation) {
            return $organisation->setBillingPeriod($period);
        }

        if(!in_array($period, ['monthly', 'annually'])) {
            throw new \Exception('Billing period must be one of "monthly" or "annually"');
        }

        $this->billing_detail->period = $period;
        $this->billing_detail->save();
    }

    /**
     * Calculate whether or not we should bill a company on a given date (defaulting to today)
     * Billing will take place monthly on the billing day (day of the month) specified in the
     * billing_detail of the organisation or user. If the billing day doesn't exist in the month
     * we are checking for (eg. 31st of November); bill on the last day of the month
     */
    public function isBillingDay($dateOfBilling=null)
    {
        if ($dateOfBilling === null) {
            $dateOfBilling = Carbon::today();
        }
        $billing = $this->billing_detail()->first();
        if (!$billing) {
            return false;
        }
        $billingDay = $billing->billing_day;
        $daysThisMonth = date('t');

        // If the billing day for this user/organisation doesn't exist this month, make their billing
        // day the last day in this month
        if ($billingDay > $daysThisMonth) {
            $billingDay = $daysThisMonth;
        }

        // If this user/organisations billing day is today, they should be billed
        return $dateOfBilling->day == $billingDay;
    }

    /**
     * Bill a user or organisation, for all billing items that are due for payment
     */
    public function bill()
    {
        // Don't charge people who are billing exempt
        if ($this->billingExempt()) {
            return true;
        }

        $chargeLog = ChargeLog::create([
            'success' => false,
            'pending' => true,
            'user_id' => $this instanceof User ? $this->id : null,
            'organisation_id' => $this instanceof Organisation ? $this->id : null,
        ]);

        $billingDetails = $this->billing_detail()->first();

        if (!$billingDetails) {
            throw new \Exception('Registration to paid service requires billing details to be setup');
        }

        $services = Service::where('is_paid_service', true)->get();
        $payingUntil = $this->calculatePayingUntil($billingDetails->period);
        $centsDue = 0;

        $billingSummary = [];
        foreach ($services as $service) {
            $priceInCents = $this->getPriceForService($service, $billingDetails);
            $billingItems = $this->getAllDueBillingItems($service);

            foreach ($billingItems as $item) {
                $itemPayment = new BillingItemPayment();
                $itemPayment->paid_until = $payingUntil;
                $itemPayment->billing_item_id = $item->id;
                $itemPayment->charge_log_id = $chargeLog->id;

                $itemPayment->save();
                $billingSummary[] = ['description' =>  json_decode($item->json_data)['company_name'], 'paidUntil' => $payingUntil->format('j M Y'), 'amount' => Billing::centsToDollars($priceInCents)];
            }
            $centsDue += $priceInCents * count($billingItems);
        }

        // Request the payment
        $totalDollarsDue = Billing::centsToDollars($centsDue);
        $success = $this->requestPayment($totalDollarsDue);

        // Update the charge log
        $gst = Billing::includingGst($totalDollarsDue);
        $chargeLog->update(['pending' => false, 'success' => $success, 'total_amount' => $totalDollarsDue, 'gst' => $gst]);

        // Above we optimistically set the paid until dates to the paying until date
        // if the payment fails we need to undo that
        if (!$success) {
            // Set all item payments 'paid until' to the last payment (or today if there hasn't been a previous payment)
            $itemPayments = $chargeLog->billingItemPayments()->get();

            foreach ($itemPayments as $item) {
                $previousPayment = BillingItemPayment::join('charge_logs', 'charge_log_id', '=', 'charge_log.id')
                                                     ->where('billing_item_id', '=', $item->billing_item_id)
                                                     ->where('charge_logs.success', '=', true)
                                                     ->orderBy('paid_until', 'desc')
                                                     ->first();

                $item->paid_until = $previousPayment ? $previousPayment->paid_until : Carbon::today();
                $item->save();
            }
        }
        else{
            $this->sendInvoices('subscription', $chargeLog->id, $billingSummary, $totalDollarsDue, $gst);
        }
        // Return whether payment was successful or not
        return $success;
    }

    private function getPriceForService($service, $billingDetails)
    {
        // If this billable entity has is directly registered with the service see if it has a specified price
        // There are times where this billable entity wont have a registration record. This is when an organisation
        // isn't registered to a service, but one of it's users is
        $registrationRecord = $this->services()->where('service_id', $service->id)->first();
        $priceInCents = $registrationRecord ? $registrationRecord->pivot->price_in_cents : null;

        if (!$priceInCents) {
            switch ($service->name) {
                case 'Good Companies':
                    $constantName = $billingDetails->period == 'monthly' ? 'constants.gc_monthly_price_in_cents' : 'constants.gc_yearly_price_in_cents';
                    $priceInCents = Config::get($constantName);
                    break;
                default:
                    throw new \Exception('Unknown default price for service');
            }
        }

        return $priceInCents;
    }

    protected function requestPayment($totalDollarsDue)
    {
        return PXPay::requestPayment($this, $totalDollarsDue);
    }

    private function calculatePayingUntil($period)
    {
        $payingUntil = Carbon::now();

         switch ($period) {
            case 'monthly':
                $payingUntil->addMonth();
                break;
            case 'annually':
                $payingUntil->addYear();
                break;
            default:
                throw new \Exception('Billing period must be one of "monthly" or "annually"');
        }

        return $payingUntil;
    }
}
