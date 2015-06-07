<?php namespace App\Models;

use Auth; // TODO: Remove
use Config;
use Carbon\Carbon;
use App\Library\Mail;
use GuzzleHttp\Client;

trait Billable {

	public function billing_detail() {
		return $this->belongsTo('App\BillingDetail');
	}

	abstract protected function memberCount();

	abstract protected function billingExempt();

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

		if(!$this->billing_detail || !$this->billing_detail->last_billed) {
			return false;
		}

		// Give user access even if last billed expires sometime today
		$now = Carbon::now();
		$now->hour = 0;
		$now->minute = 1;

		// TODO: Should we store paid_until instead of last_billed + period
		$validUntil = $this->billing_detail->last_billed;
		if($this->billing_detail->period === 'monthly') {
			$validUntil->addMonth();
		}
		else {
			$validUntil->addYear();
		}

		return $now->lt($validUntil);
	}

	public function hasBrowserAccess() {
		return $this->billingExempt() || $this->inTrial() || $this->isPaid();
	}

	public function everBilled() {
		$organisation = $this->organisation;

		if($organisation) {
			return $organisation->everBilled();
		}

		return $this->billing_detail && $this->billing_detail->last_billed;
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
	 * true on success, false otherwise.
	 *
	 * @return bool
	 */
	public function charge() {
		$organisation = $this->organisation;

		if($organisation) {
			return $organisation->charge();
		}

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

		// Number of users * period to bill for
		$price = bcmul($periodCost, (string)$this->memberCount(), 2);

		if(!env('DISABLE_PAYMENT', false)) {

			$xmlRequest = view('billing.pxpost', [
				'postUsername' => env('PXPOST_USERNAME', ''),
				'postPassword' => env('PXPOST_KEY', ''),
				'amount' => $price,
				'dpsBillingId' => $this->billing_detail->dps_billing_token,
				'id' => $this->billing_detail->id,
			])->render();

			$postClient = new Client(['base_uri' => 'https://sec.paymentexpress.com']);

			$response = $postClient->post('pxpost.aspx', ['body' => $xmlRequest]);

			$xmlResponse = new \SimpleXMLElement((string)$response->getBody());

			if(!boolval((string)$xmlResponse->Success)) {
				return false;
			}

		}

		$this->billing_detail->last_billed = Carbon::now();
		$this->billing_detail->save();

		// TODO: Get actual user when this is run as command instead of from billing start
		$user = Auth::user();
		Mail::sendStyledMail('emails.invoice', compact('user'), $user->getEmailForPasswordReset(), $user->fullName(), 'CataLex | Invoice/Receipt');

		return true;
	}
}
