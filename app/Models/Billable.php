<?php namespace App\Models;

use App\Trial;
use League\Flysystem\Exception;
use Log;
use Config;
use App\ChargeLog;
use Carbon\Carbon;
use App\Library\PXPay;
use App\Library\Billing;
use App\Service;
use App\BillingItem;
use App\BillingItemPayment;
use App\BillingDetail;

trait Billable
{

    abstract public function billableType();

    abstract public function shouldBill();

    abstract public function billingExempt();

    abstract public function paymentAmount();

    abstract protected function getAllDueBillingItems($service);

    abstract public function hasBillingSetup();

    abstract public function accountNumber();

    public function billing_detail()
    {
        return $this->belongsTo(BillingDetail::class);
    }

    public function chargeLogs()
    {
        return $this->hasMany(ChargeLog::class);
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

    public function trials()
    {
        return $this->hasMany(Trial::class);
    }

    public function foreignIdName()
    {
        switch ($this->billableType()) {
            case 'user':
                return 'user_id';

            case 'organisation':
                return 'organisation_id';

            default:
                throw new Exception('Unknown billable type');
        }
    }

    public function getBillableEntity()
    {
        return $this->organisation ?: this;
    }

    /**
     * Check if a users subscription is up-to-date
     *
     * @return bool
     */
    public function subscriptionUpToDate()
    {
        // A billable entity subscription is up-to-date if their last change log was successful (or is pending).
        // If a billable entity has an organisation, their subscription is their organisations subscription.

        // First: check if this billable entity has an organisation
        if ($this->organisation) {
            return $this->organisation->subscriptionUpToDate();
        }

        // Check if the user/organisation is exempt from billing
        if ($this->billingExempt()) {
            return true;
        }

        // Check if the last change log failed
        // If there is no charge log, then the user/organisation hasn't been billed - so they are up-to-date
        $chargeLog = $this->chargeLogs()->orderBy('timestamp', 'DESC')->first();

        if (!$chargeLog) {
            return true;
        }

        return $chargeLog->status() !== ChargeLog::FAILED;
    }

    public function hasAccess(Service $service)
    {
        if ($this->roles && $this->hasRole('global_admin')) {
            return true;
        }

        if ($this->free) {
            return true;
        }
        // If this billable entity has an organisation, fallback to the organisation's access level
        if ($this->organisation) {
            return $this->organisation->hasAccess($service);
        }

        $billablesService = $this->services()->where('service_id', $service->id)->first();

        if($billablesService && $billablesService->is_paid_service && !$this->billing_detail()->first()) {
            return false;
        }

        // If this billable entity is registered for this service, check their service level
        if ($billablesService != null) {
            return $billablesService->pivot->access_level == 'full_access';
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
        if ($this->organisation) {
            return $this->organisation->setBillingPeriod($period);
        }
        else {
            if (!in_array($period, ['monthly', 'annually'])) {
                throw new \Exception('Billing period must be one of "monthly" or "annually"');
            }

            $this->billing_detail()->update(['period' => $period]);
        }
    }

    /**
     * Calculate whether or not we should bill a company on a given date (defaulting to today)
     * Billing will take place monthly on the billing day (day of the month) specified in the
     * billing_detail of the organisation or user. If the billing day doesn't exist in the month
     * we are checking for (eg. 31st of November); bill on the last day of the month
     */
    public function isBillingDay($dateOfBilling = null)
    {
        $billing = $this->billing_detail()->first();

        // If there is billing setup, they don't have a billing day
        if (!$billing) {
            return false;
        }

        $dateOfBilling = $dateOfBilling ?: Carbon::today();
        $billingDay = $billing->billing_day;
        $daysThisMonth = $dateOfBilling->format('t');

        // If the billing day for this user/organisation doesn't exist this month, make their billing
        // day the last day in this month
        if ($billingDay > $daysThisMonth) {
            $billingDay = $daysThisMonth;
        }

        // If this user/organisations billing day is today, they should be billed
        return $dateOfBilling->day == $billingDay;
    }

    public function needsBilled()
    {
        $services = Service::where('is_paid_service', true)->get();
        $totalBillingItems = 0;

        foreach ($services as $service) {
            $billingItems = $this->getAllDueBillingItems($service);
            $totalBillingItems += count($billingItems);
        }

        return $totalBillingItems > 0;
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

        // Get a list of paid CataLex services
        $services = Service::where('is_paid_service', true)->get();

        // Check if they have any services that require billing
        if ($services->count() == 0) {
            return true;
        }

        $chargeLog = ChargeLog::create([
            'success' => false,
            'pending' => true,
            $this->foreignIdName() => $this->id,
        ]);

        // Check the user/organisation has billing setup
        $billingDetails = $this->billing_detail()->first();

        if (!$billingDetails) {
            $chargeLog->update(['pending' => false]);

            $billableType = $this instanceof User ? 'user' : 'organisation';
            Log::error('Tried to bill ' . $billableType . ' with id ' . $this->id . ', but failed because they have no billing details');

            return false;
        }

        $payingUntil = $this->calculatePayingUntil($billingDetails->period);
        $centsDue = 0;

        $billingSummary = [];
        foreach ($services as $service) {
            $priceInCents = $this->getPriceForService($service, $billingDetails->period);
            $billingItems = $this->getAllDueBillingItems($service);

            foreach ($billingItems as $item) {
                $itemPayment = new BillingItemPayment();
                $itemPayment->paid_until = $payingUntil;
                $itemPayment->billing_item_id = $item->id;
                $itemPayment->charge_log_id = $chargeLog->id;
                $itemPayment->amount = Billing::centsToDollars($priceInCents);
                $itemPayment->gst= Billing::includingGst($priceInCents);

                $itemPayment->save();

                $billingSummary[] = [
                    'description' =>  json_decode($item->json_data, true)['company_name'],
                    'paidUntil' => $itemPayment->paid_until->format('j M Y'),
                    'amount' => $itemPayment->amount,
                ];
            }
            $centsDue += $priceInCents * count($billingItems);
        }

        // Handle discounts
        $totalBeforeDiscount = Billing::centsToDollars($centsDue);

        $discountPercent = $billingDetails->getDiscountPercent();
        $totalAfterDiscount = $discountPercent ? Billing::applyDiscount($totalBeforeDiscount, $discountPercent) : $totalBeforeDiscount;

        // Request payment
        $success = $this->requestPayment($totalAfterDiscount);

        // Update the charge log
        $chargeLog->update([
            'success' => $success,
            'pending' => false,
            'total_before_discount' => $totalBeforeDiscount,
            'discount_percent' => $discountPercent,
            'total_amount' => $totalAfterDiscount,
            'gst' => Billing::includingGst($totalAfterDiscount),
        ]);

        // Above we optimistically set the paid until dates to the paying until date
        // if the payment fails we need to undo that
        if ($chargeLog->success) {
            $chargeLog->sendInvoices();
        }
        else {
            // Set all item payments 'paid until' to the last payment (or today if there hasn't been a previous payment)
            $itemPayments = $chargeLog->billingItemPayments()->get();

            foreach ($itemPayments as $item) {
                $previousPayment = BillingItemPayment::join('charge_logs', 'charge_log_id', '=', 'charge_logs.id')
                    ->where('billing_item_id', '=', $item->billing_item_id)
                    ->where('charge_logs.success', '=', true)
                    ->orderBy('paid_until', 'desc')
                    ->first();

                $item->paid_until = $previousPayment ? $previousPayment->paid_until : Carbon::today();
                $item->save();
            }

            $chargeLog->sendFailedNotice();
        }

        // Return whether payment was successful or not
        return $chargeLog->success;
    }

    private function getPriceForService($service, $billingPeriod)
    {
        // If this billable entity has is directly registered with the service see if it has a specified price
        // There are times where this billable entity wont have a registration record. This is when an organisation
        // isn't registered to a service, but one of it's users is
        $registrationRecord = $this->services()->where('service_id', $service->id)->first();

        if ($registrationRecord && $registrationRecord->pivot->price_in_cents) {
            return $registrationRecord->pivot->price_in_cents;
        }

        switch ($service->name) {
            case 'Good Companies':
                $constantName = $billingPeriod == 'monthly' ? 'constants.gc_monthly_price_in_cents' : 'constants.gc_yearly_price_in_cents';
                return Config::get($constantName);

            case 'CataLex Sign':
                $constantName = $billingPeriod == 'monthly' ? 'constants.sign_monthly_price_in_cents' : 'constants.sign_yearly_price_in_cents';
                return Config::get($constantName);

            default:
                throw new \Exception('Unknown default price for service');
        }
    }

    protected function requestPayment($totalDollarsDue)
    {
        $pxPay = new PXPay();
        $success = $pxPay->requestPayment($this, $totalDollarsDue);

        return $success;
    }

    private function calculatePayingUntil($period)
    {
         switch ($period) {
            case 'monthly':
                return Carbon::now()->addMonthsNoOverflow(1);

            case 'annually':
                return Carbon::now()->addYear();

            default:
                throw new \Exception('Billing period must be one of "monthly" or "annually"');
        }
    }
}
