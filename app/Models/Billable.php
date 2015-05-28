<?php namespace App\Models;

use Config;
use Carbon\Carbon;

trait Billable {

	public function billing_detail() {
		return $this->belongsTo('App\BillingDetail');
	}

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
		return $this->inTrial() || $this->isPaid();
	}

	public function everBilled() {
		$organisation = $this->organisation;

		if($organisation) {
			return $organisation->everBilled();
		}

		return $this->billing_detail  && $this->billing_detail->last_billed;
	}
}
