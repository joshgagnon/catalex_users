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

trait Billable
{
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

    abstract public function billableType();

    abstract public function shouldBill();

    abstract public function billingExempt();

    abstract public function paymentAmount();

    abstract public function sendInvoices($invoiceNumber, $totalAmount, $gst, $listItem, $orgName=null, $orgId=null);

    abstract protected function getAllDueBillingItems($service);

    abstract public function hasBillingSetup();

    abstract public function accountNumber();

    public function getBillableEntity()
    {
        if ($this->organisation) {
            return $this->organisation->getBillableEntity();
        }

        return $this;
    }

    public function inTrial()
    {
        $organisation = $this->organisation;

        if ($organisation) {
            return $organisation->inTrial();
        }

        return $this->created_at->diffInMinutes(Carbon::now()) < Config::get('constants.trial_length_minutes');
    }

    public function subscriptionUpToDate()
    {
        if ($this->organisation) {
            return $this->organisation->subscriptionUpToDate();
        }

        // Get the latest charge log
        $chargeLog = $this->chargeLogs()->orderBy('timestamp', 'DESC')->first();

        // If there is no charge log, then the user hasn't been billed - so they are up-to-date
        if (!$chargeLog) {
            return true;
        }

        // Charge log was successful or is still pending = supscription is up-to-date
        if ($chargeLog->success || $chargeLog->pending) {
            return true;
        }

        return false;
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
        $organisation = $this->organisation;

        if ($organisation) {
            return $organisation->setBillingPeriod($period);
        }

        if (!in_array($period, ['monthly', 'annually'])) {
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
        $dateOfBilling = $dateOfBilling ? : Carbon::today();
        $billing = $this->billing_detail()->first();
        if (!$billing) {
            return false;
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
     * Bill a user or organisation, for all billing items that are due for payment
     */
    public function bill()
    {
        // Don't charge people who are billing exempt
        if ($this->billingExempt()) {
            return true;
        }

        // Get the user's services
        $services = Service::where('is_paid_service', true)->get();

        // Check if they have any services that require billing
        if ($services->count() == 0) {
            return true;
        }

        $chargeLog = ChargeLog::create([
            'success' => false,
            'pending' => true,
            'user_id' => $this->billableType() == 'user' ? $this->id : null,
            'organisation_id' => $this->billableType() == 'organisation' ? $this->id : null,
        ]);

        // Check the user has billing setup
        $billingDetails = $this->billing_detail()->first();

        if (!$billingDetails) {
            $chargeLog->update(['pending' => false, 'success' => false]);

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
                $previousPayment = BillingItemPayment::join('charge_logs', 'charge_log_id', '=', 'charge_logs.id')
                                                     ->where('billing_item_id', '=', $item->billing_item_id)
                                                     ->where('charge_logs.success', '=', true)
                                                     ->orderBy('paid_until', 'desc')
                                                     ->first();

                $item->paid_until = $previousPayment ? $previousPayment->paid_until : Carbon::today();
                $item->save();
            }

            $chargeLog->sendFailedNotice();
        } else if ($totalDollarsDue > 0) {
            $chargeLog->sendInvoices();
        }

        // Return whether payment was successful or not
        return $success;
    }

    private function getPriceForService($service, $billingPeriod)
    {
        // If this billable entity has is directly registered with the service see if it has a specified price
        // There are times where this billable entity wont have a registration record. This is when an organisation
        // isn't registered to a service, but one of it's users is
        $registrationRecord = $this->services()->where('service_id', $service->id)->first();
        $priceInCents = $registrationRecord ? $registrationRecord->pivot->price_in_cents : null;

        if (!$priceInCents) {
            switch ($service->name) {
                case 'Good Companies':
                    $constantName = $billingPeriod == 'monthly' ? 'constants.gc_monthly_price_in_cents' : 'constants.gc_yearly_price_in_cents';
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
        $pxPay = new PXPay();
        $success = $pxPay->requestPayment($this, $totalDollarsDue);

        return $success;
    }

    private function calculatePayingUntil($period)
    {
        $payingUntil = Carbon::now();

         switch ($period) {
            case 'monthly':
                $payingUntil->addMonthsNoOverflow(1);
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
