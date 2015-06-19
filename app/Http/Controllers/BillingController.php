<?php namespace App\Http\Controllers;

use Auth;

use Illuminate\Http\Request;

class BillingController extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->middleware('auth');

		$user = Auth::user();

		// Don't allow non-admin members of an org to try to pay
		if($user && $user->organisation && !$user->can('edit_own_organisation')) {
			abort(403, 'Forbidden');
		}
	}

	public function getStart(Request $request) {
		return view('billing.confirm-subscription');
	}

	public function postStart(Request $request) {
		$period = $request->get('billing_period');

		if(!in_array($period, ['monthly', 'annually'])) {
			return redirect()->back()->withErrors(['Please select a billing period.']);
		}

		$user = Auth::user();

		$user->setBillingPeriod($period);

		if($user->rebill()) {
			return redirect()->action('BillingController@getConfirmStart');
		}

		return redirect()->back()->withErrors(['We were unable to charge your card at this time. Please try again shortly.']);
	}

	public function getConfirmStart() {
		return view('billing.subscription-success');
	}
}
