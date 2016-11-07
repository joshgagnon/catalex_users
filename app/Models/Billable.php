<?php namespace App\Models;

use Log;
use Config;
use App\ChargeLog;
use Carbon\Carbon;
use App\Library\Mail;
use GuzzleHttp\Client;
use App\Library\Billing;

trait Billable {

	public function billing_detail() {
		return $this->belongsTo('App\BillingDetail');
	}

	abstract public function billingExempt();

	abstract public function paymentAmount();

	abstract public function sendInvoices($type, $invoiceNumber, $listItem, $orgName=null, $orgId=null);

	public function inTrial() {
		$organisation = $this->organisation;

		if($organisation) {
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

	public function hasBrowserAccess() {
		return true; ///$this->billingExempt() || $this->inTrial() || $this->isPaid();
	}

	public function hasSignAccess() {
		return true;
	}

    public function hasGoodCompaniesAccess() {
        return true; //$this->billingExempt() || $this->inTrial() || $this->isPaid();
    }

	public function everBilled() {
		$organisation = $this->organisation;

		if($organisation) {
			return $organisation->everBilled();
		}

		return $this->billing_detail && $this->paid_until !== null;
	}

	public function setBillingPeriod($period) {
		$organisation = $this->organisation;

		if($organisation) {
			return $organisation->setBillingPeriod($period);
		}

		if(!in_array($period, ['monthly', 'annually'])) {
			throw new Exception('Billing period must be one of "monthly" or "annually"');
		}

		$this->billing_detail->period = $period;
		$this->billing_detail->save();
	}

	/**
	 * Charge the user or organisation based on number of members and billing period. Returns
	 * true if already up to date or on billing success, false otherwise.
	 *
	 * @return bool
	 */
	public function rebill() {
		if($this->billingExempt()) return true;

		// Delegate rebilling to organisation if present
		if($this->organisation) {
			return $this->organisation->rebill();
		}


		// Is this already paid for today?
		if($this->paid_until && Carbon::now()->lt($this->paid_until)) return true;

		$payingUntil = Carbon::now();

		switch($this->billing_detail->period) {
			case 'monthly':
				$payingUntil->addMonth();
				$intervalAmount = Config::get('constants.monthly_price');
				$description = '1 Month';
				break;
			case 'annually':
				$payingUntil->addYear();
				$intervalAmount = Config::get('constants.annual_price');
				$description = '1 Year';
				break;
			default:
				throw new Exception('Billing period must be one of "monthly" or "annually"');
		}

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

		if(!boolval((string)$xmlResponse->Success)) {
			$log->success = false;
			$log->save();

			return false;
		}

		$log->success = true;
		$log->save();

		return true;
	}
}
