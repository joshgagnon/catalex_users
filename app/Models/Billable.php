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

    public function billing_detail() {
        return $this->belongsTo(BillingDetail::class);
    }

    /**
     * Services this user or organisation is registered to
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_registrations')->withPivot('price_in_cents', 'period', 'access_level')->withTimestamps();
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

    abstract public function sendInvoices($type, $invoiceNumber, $listItem, $orgName=null, $orgId=null);

    public function inTrial() {
        $organisation = $this->organisation;

        if ($organisation) {
            return $organisation->inTrial();
        }

        return $this->created_at->diffInMinutes(Carbon::now()) < Config::get('constants.trial_length_minutes');
    }

    public function isPaid() {
        $organisation = $this->organisation;

        if($organisation) {
            return $organisation->isPaid();
        }

        if(!$this->paid_until) return false;

        return Carbon::now()->lt($this->paid_until->hour(23)->minute(59));
    }

    public function hasBrowserAccess()
    {
        return true; ///$this->billingExempt() || $this->inTrial() || $this->isPaid();
    }

    public function hasSignAccess()
    {
        return true;
    }

    public function hasGoodCompaniesAccess()
    {
        return true;
    }

    public function hasAccess(Service $service)
    {
        $billablesService = $this->services()->where('service_id', $service->id)->first();
        
        // If this billable entity is registered for this service, check their service level
        if ($billablesService != null) {
            return $billablesService->pivot->access_level == 'full_access';
        }

        // If this billable entity has an organisation, fallback to the organisations access level
        if ($this->organisation) {
            return $this->organisation->hasAccess($service);
        }

        // If not registered for the service and not belonging to an organisation: this billable entity has no access
        return false;
    }

    public function everBilled() {
        $organisation = $this->organisation;

        if ($organisation) {
            return $organisation->everBilled();
        }

        return $this->billing_detail && $this->paid_until !== null;
    }

    public function setBillingPeriod($period) {
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

        $billingDay = $this->billing_detail()->first()->billing_day;
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

        // Delegate billing to organisation if present
        if ($this->organisation) {
            return $this->organisation->bill();
        }

        $chargeLog = ChargeLog::create([
            'success' => false,
            'pending' => true,
            'user_id' => $this instanceof User ? $this->id : null,
            'organisation_id' => $this instanceof Organisation ? $this->id : null,
        ]);

        // Bill for all of this billable entity's services
        $services = $this->services()->where('is_paid_service', true)->get();
        $centsDue = 0;

        foreach ($services as $service) {
            $registrationRecord = $this->services()->where('service_id', $service->id)->first();

            if (!$registrationRecord->pivot->period) {
                throw new \Exception('Registration to paid service requires billing period. Billing period must be either "monthly" or "annually.');
            }

            if (!$registrationRecord->pivot->price_in_cents) {
                throw new \Exception('Registration to paid service requires billing price in cents.');
            }

            $itemsDue = $this->billingItems()->where('service_id', $service->id)->dueForPayment()->get();
            $payingUntil = $this->calculatePayingUntil($registrationRecord->pivot->period);

            foreach ($itemsDue as $item) {
                $itemPayment = new BillingItemPayment();
                $itemPayment->paid_until = $payingUntil;
                $itemPayment->billing_item_id = $item->id;
                $itemPayment->charge_log_id = $chargeLog->id;

                $itemPayment->save();
            }

            $centsDue += $registrationRecord->pivot->price_in_cents * count($itemsDue);
        }

        $totalDollarsDue = Billing::centsToDollars($centsDue);
        $success = PXPay::requestPayment($this, $totalDollarsDue);

        $chargeLog->pending = false;
        $chargeLog->succes = $success;
        $chargeLog->amount = $totalDollarsDue;
        $chargeLog->gst = Billing::includingGst($totalDollarsDue);
        $chargeLog->save();

        // Set all item payments 'paid until' to the last payment (or today if there hasn't been a previous payment)
        if (!$success) {
            $itemPayments = $chargeLog->billingItemPayments()->get();

            foreach ($itemPayments as $item) {
                $previousPayment = BillingItemPayment::where('billing_item_id', '=', $item->billing_item_id)
                                                     ->where('charge_log_id', '!=', $item->charge_log_id)
                                                     ->orderBy('paid_until', 'desc')
                                                     ->first();

                $item->paid_until = $previousPayment ? $previousPayment->paid_until : Carbon::today();
                $item->save();
            }
        }

        // Return whether payment was successful or not
        return $success;
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

    /**
     * Charge the user or organisation based on number of members and billing period. Returns
     * true if already up to date or on billing success, false otherwise.
     *
     * @return bool
     */
    public function rebill() {
        if ($this->billingExempt()) {
            return true;
        }

        // Delegate rebilling to organisation if present
        if($this->organisation) {
            return $this->organisation->rebill();
        }

        // Is this already paid for today?
        if ($this->paid_until && Carbon::now()->lt($this->paid_until)) {
            return true;
        }

        $payingUntil = $this->calculatePayingUntilCarbon($this->billing_detail->period);

        $paymentAmount = $this->paymentAmount();
        if(!$this->charge($paymentAmount)) {
            return false;
        }

        $this->paid_until = $payingUntil;
        $this->save();

        // Update all members
        if($this->members) {
            foreach($this->members as $member) {
                $member->paid_until = $payingUntil;
                $member->save();
            }
        }

        $listItem = [
            'Subscription to Law Browser &mdash; ' . $description,
            $this->members ? $this->members->count() : 1,
            $intervalAmount,
            $paymentAmount,
        ];
        $this->sendInvoices('subscription', 1, $listItem); // TODO: Invoice number from charge_log->id

        return true;
    }

    /**
     * Charge the user or organisation $amount NZD. Returns true on success, false otherwise.
     *
     * @param  string $amount
     * @return bool
     */
    private function charge($amount) {
        $log = new ChargeLog([
            'success' => false,
            'user_id' => $this instanceof \App\User ? $this->id : null,
            'organisation_id' => $this instanceof \App\Organisation ? $this->id : null,
            'total_amount' => $amount,
            'gst' => Billing::includingGst($amount),
        ]);

        if(env('DISABLE_PAYMENT', false)) {
            Log::info('Simulated charge of $' . $amount . ' to ' . get_class($this) . ' ' . $this->id);

            $log->success = true;
            $log->save();

            return true;
        }

        $xmlRequest = view('billing.pxpost', [
            'postUsername' => env('PXPOST_USERNAME', ''),
            'postPassword' => env('PXPOST_KEY', ''),
            'amount' => $amount,
            'dpsBillingId' => $this->billing_detail->dps_billing_token,
            'id' => $this->billing_detail->id,
        ])->render();

        $postClient = new Client(['base_uri' => 'https://sec.paymentexpress.com']);

        $response = $postClient->post('pxpost.aspx', ['body' => $xmlRequest]);

        $xmlResponse = new \SimpleXMLElement((string)$response->getBody());

        if (!boolval((string)$xmlResponse->Success)) {
            $log->success = false;
            $log->save();

            return false;
        }

        $log->success = true;
        $log->save();

        return true;
    }
}
