<?php namespace App\Models;

use App\BillingDetail;
use App\BillingItem;
use App\BillingItemPayment;
use App\ChargeLog;
use App\Library\Billing;
use App\Library\PXPay;
use App\Service;
use App\Trial;
use Carbon\Carbon;
use Config;
use League\Flysystem\Exception;
use Log;

trait Billable
{
    abstract public function billableType();

    abstract public function shouldBill();

    abstract public function billingExempt();

    abstract public function paymentAmount();

    abstract protected function getAllDueBillingItems($service);

    abstract public function hasBillingSetup();

    abstract public function accountNumber();

    abstract public function isSubscribedTo($serviceId);

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
        return $this->belongsToMany(Service::class, 'service_registrations')->withTimestamps();
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

        if ($billablesService && $billablesService->is_paid_service && !$this->billing_detail()->first()) {
            return false;
        }

        // If this billable entity is registered for this service, check their service level
        if ($billablesService != null) {
            return true;
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
     *
     * @param null $dateOfBilling
     * @return bool
     */
    public function isBillingDay($dateOfBilling = null)
    {
        $billing = $this->billing_detail()->first();

        // If there is billing setup, they don't have a billing day
        if (!$billing) {
            return false;
        }
        $dateOfBilling = $dateOfBilling ?: Carbon::today();
        if($billing->period === 'annually') {

            if($billing->created_at->month !== $dateOfBilling->month) {
                return false;
            }
        }

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
     * Get a list of paid CataLex services that the current user is subscribed to
     *
     * @return array
     */
    public function subscribedServices()
    {
        // This wont scale: it is a n + 1 query, but it will do for now.
        $paidServices = Service::where('is_paid_service', true)->get();
        $billableServices = [];

        foreach ($paidServices as $service) {
            if ($this->isSubscribedTo($service->id)) {
                $billableServices[] = $service;
            }
        }

        return $billableServices;
    }

    /**
     * Bill a user or organisation, for all billing items that are due for payment
     *
     * @return bool
     */
    public function bill()
    {
        // Don't charge people who are billing exempt


        if ($this->billingExempt()) {
            return true;
        }

        // Get a list of paid CataLex services that the current user is subscribed to
        $services = $this->subscribedServices();

        // Check if they have any services that require billing
        if (count($services) === 0) {
            return true;
        }

        // Create the charge log record for this bill - it is currently pending, but not yet successful
        $chargeLog = ChargeLog::create([$this->foreignIdName() => $this->id, 'success' => false, 'pending' => true]);
        $chargeLog = $chargeLog->fresh(); // The create method doesn't populate the model instance with DB defaults, so call fresh() to fully populate the model instance

        // If this user doesn't have billing setup, fail the bill and exit the biling process
        if (!$this->hasBillingSetup()) {
            $chargeLog->update(['pending' => false]);

            $billableType = $this instanceof User ? 'user' : 'organisation';
            Log::error('Tried to bill ' . $billableType . ' with id ' . $this->id . ', but failed because they have no billing details');

            return false;
        }

        $billingDetails = $this->billing_detail()->first();
        $payingUntil = $this->calculatePayingUntil($billingDetails->period);
        $centsDue = 0;

        $itemPaymentsToCreate = [];

        foreach ($services as $service) {
            $billingItems = $this->getAllDueBillingItems($service);
            foreach ($billingItems as $item) {
                $priceInCents = $this->priceForBillingItem($item->item_type, $billingDetails->period);
                $centsDue += $priceInCents;

                $itemPaymentsToCreate[] = [
                    'paid_until'      => $payingUntil,
                    'billing_item_id' => $item->id,
                    'charge_log_id'   => $chargeLog->id,
                    'amount'          => Billing::centsToDollars($priceInCents),
                    'gst'             => Billing::includingGst($priceInCents),
                    'created_at'      => Carbon::now(),
                    'updated_at'      => Carbon::now(),
                ];
            }
        }

        BillingItemPayment::insert($itemPaymentsToCreate);

        // Handle discounts
        $totalBeforeDiscount = Billing::centsToDollars($centsDue);

        $discountPercent = $billingDetails->getDiscountPercent();
        $totalAfterDiscount = $discountPercent ? Billing::applyDiscount($totalBeforeDiscount, $discountPercent) : $totalBeforeDiscount;

        if ($this->is_invoice_customer) {
            // Update the charge log
            $chargeLog->update([
                'success'               => false,
                'pending'               => true,
                'total_before_discount' => $totalBeforeDiscount,
                'discount_percent'      => $discountPercent,
                'total_amount'          => $totalAfterDiscount,
                'gst'                   => Billing::includingGst($totalAfterDiscount),
                'payment_type'          => ChargeLog::PAYMENT_TYPE_INVOICE,
            ]);

            $this->sendInvoices($chargeLog);

            return true;
        }
        else {
            // Request payment
            $success = $this->requestPayment($totalAfterDiscount);

            // Update the charge log
            $chargeLog->update([
                'success'               => $success,
                'pending'               => false,
                'total_before_discount' => $totalBeforeDiscount,
                'discount_percent'      => $discountPercent,
                'total_amount'          => $totalAfterDiscount,
                'gst'                   => Billing::includingGst($totalAfterDiscount),
            ]);

            // Above we optimistically set the paid until dates to the paying until date
            // if the payment fails we need to undo that
            if ($chargeLog->success) {
                $this->sendInvoices($chargeLog);
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

            return $chargeLog->success;
        }
    }

    /**
     * Extract this, so we can override it in testing
     *
     * @param \App\ChargeLog $chargeLog
     */
    protected function sendInvoices(ChargeLog $chargeLog)
    {
        $chargeLog->sendInvoices();
    }

    /**
     * Get the price for an individual billing item.
     *
     * @param $itemType
     * @param $billingPeriod
     * @return mixed
     * @throws \Exception
     */
    private function priceForBillingItem($itemType, $billingPeriod)
    {
        switch ($itemType) {
            case BillingItem::ITEM_TYPE_GC_COMPANY:
                $constantName = $billingPeriod == 'monthly' ? 'constants.gc_company_monthly' : 'constants.gc_company_yearly';
                return Config::get($constantName);

            case BillingItem::ITEM_TYPE_SIGN_SUBSCRIPTION:
                $constantName = $billingPeriod == 'monthly' ? 'constants.sign_monthly' : 'constants.sign_yearly';
                return Config::get($constantName);

            case BillingItem::ITEM_TYPE_COURT_COSTS_SUBSCRIPTION:
                $constantName = $billingPeriod == 'monthly' ? 'constants.court_costs_monthly' : 'constants.court_costs_yearly';
                return Config::get($constantName);

            default:
                throw new \Exception('Unknown default price for item');
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
