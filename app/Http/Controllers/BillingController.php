<?php

namespace App\Http\Controllers;

use App\BillingItem;
use App\ChargeLog;
use App\Library\BillingItemSummariser;
use App\Service;
use App\Trial;
use Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Library\PXPay;
use App\Library\Billing;
use App\BillingDetail;

class BillingController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');

        $user = Auth::user();

        // Don't allow non-admin members of an org to try to pay
        if ($user && $user->organisation && !$user->can('edit_own_organisation')) {
            abort(403, 'Forbidden');
        }
    }

    public function index(Request $request)
    {
        $billable = $request->user();
        $billableKeyName = 'subject';

        if ($billable->organisation) {
            $billable = $billable->organisation;
            $billableKeyName = 'organisation';
        }

        $chargeLogs = $billable->chargeLogs()->orderBy('timestamp', 'DESC')->get();
        $billingItems = (new BillingItemSummariser($billable))->summarise();
        $subscriptionUpToDate = Auth::user()->subscriptionUpToDate();

        return view('billing.index')->with([
            'subscriptionUpToDate' => $subscriptionUpToDate,
            'chargeLogs' => $chargeLogs,
            'billingItems' => $billingItems,
            $billableKeyName => $billable,
        ]);
    }

    public function getStart(Request $request)
    {
        return view('billing.confirm-subscription');
    }

    public function postStart(Request $request)
    {
        $period = $request->get('billing_period');

        if(!in_array($period, ['monthly', 'annually'])) {
            return redirect()->back()->withErrors(['Please select a billing period.']);
        }

        $user = Auth::user();

        $user->setBillingPeriod($period);

        if($user->rebill()) {
            return redirect()->route('BillingController@getConfirmStart');
        }

        return redirect()->back()->withErrors(['We were unable to charge your card at this time. Please try again shortly.']);
    }

    public function getConfirmStart()
    {
        return view('billing.subscription-success');
    }

    public function edit()
    {
        // Get the user (or their organisation's) billing details
        $user = Auth::user();
        $billableEntity = $user->organisation ? $user->organisation : $user;
        $billingDetails = $billableEntity->billing_detail()->first();

        $subscriptionUpToDate = $user->subscriptionUpToDate();

        return view('billing.edit')->with([
            'billingDetails' => $billingDetails,
            'subscriptionUpToDate' => $subscriptionUpToDate
        ]);
    }

    public function update(Request $request)
    {
        if (empty($request->period) && $request->period != 'monthly' && $request->period != 'annually') {
            return redirect()->back()->withErrors('Billing period must be either monthly or annually.');
        }

        $billableEntity = Auth::user()->getBillableEntity();
        $billableEntity->billing_detail()->update(['period' => $request->period]);

        return redirect()->route('billing.edit')->withSuccess('Billing period updated.');
    }

    public function delete()
    {
        $billableEntity = Auth::user()->getBillableEntity();

        $billableEntity->billing_detail()->delete();
        $billableEntity->update(['billing_detail_id' => null]);

        return redirect()->route('billing.edit')->withSuccess('Card deleted');
    }

    public function retryBilling()
    {
        $billableEntity = Auth::user()->getBillableEntity();

        if (!$billableEntity->subscriptionUpToDate() && $billableEntity->needsBilled()) {
            $success = $billableEntity->bill();

            if ($success) {
                return redirect()->route('billing.edit')->withSuccess('Billing complete.');
            }
            else {
                return redirect()->route('billing.edit')->withError('Billing failed. Please check your card details.');
            }
        }

        return redirect()->route('billing.edit')->withSuccess('Your billing is already up-to-date.');
    }

    public function createCard(Request $request)
    {
        $billableEntity = Auth::user()->getBillableEntity();

        // Don't allow non-admin members of an org to try to pay
        if ($billableEntity->billing_detail_id) {
            return redirect()->route('billing.edit')->withErrors('You already have a card setup, please remove it to add a new one.');
        }

        // Create a new payment gayway request to get iframe url to show
        $gateway = PXPay::getGateway();

        // Start the request to DPS
        $response = $gateway->createCard([
            'returnUrl' => route('billing.store-card'),
            'currency' => PXPay::CURRENCY_NZD,
        ])->send();

        if (!$response->isRedirect()) {
            return redirect()->back()->withErrors(['An error occurred contacting the payment gateway, please try again.']);
        }

        return view('billing.register-card')->with([
            'gatewayURL' => $response->getRedirectUrl()
        ]);
    }

    public function finishCreateCard(Request $request)
    {
        $user = Auth::user();
        $billableEntity = $user->organisation ? $user->organisation : $user;

        if (!$billableEntity->billing_detail) {
            return redirect()->back()->withErrors('Error with card setup, please try again');
        }

        if (empty($request->period) && $request->period != 'monthly' && $request->period != 'annually') {
            return redirect()->back()->withErrors('Billing period must be either monthly or annually.');
        }

        $billableEntity = Auth::user()->getBillableEntity();
        $billableEntity->billing_detail()->update(['period' => $request->period]);

        if ($request->session()->has('redirect_route_name')) {
            $routeName = $request->session()->pull('redirect_route_name');
            $data = $request->session()->has('redirect_data') ? $request->session()->pull('redirect_data') : [];

            return redirect()->route($routeName, $data);
        }

        return redirect()->route('billing.edit')->withSuccess('Card successfully added');
    }

    public function storeCard(Request $request)
    {
        // Use result query param to get real auth result data
        $gateway = PXPay::getGateway();
        $response = $gateway->completeCreateCard()->send();
        $responseData = $response->getData();

        // If the completion process failed, return the failed view
        if (boolval((string)$responseData->Success) === false) {
            return view('billing.frames.pxpay-failed');
        }

        $billableEntity = Auth::user()->getBillableEntity();
        $billingDetails = $billableEntity->billing_detail()->first();

        // Get all the data we need to update the billing details
        $billingDetailData = [
            'period' => $request->session()->has('billing_period') ? $request->session()->pull('billing_period') : 'monthly', // Default: monthly
            'dps_billing_token' => (string)$responseData->DpsBillingId,
            'expiry_date' => (string)$responseData->DateExpiry,
            'masked_card_number' => (string)$responseData->CardNumber,
        ];

        if ($billingDetails) {
            $billingDetails->update($billingDetailData); // This user already has billing details - so update them
        }
        else {
            // This user or organisation hasn't already got billing details setup; create their billing details
            $trial = Trial::findOrCreate($billableEntity, 'Good Companies');
            $billingDate = Carbon::now()->lte($trial->end_date) ? $trial->end_date->addDays(1) : Carbon::today();

            // Create the billing details and attach it to the billable entity
            $billingDetails = BillingDetail::create($billingDetailData + ['billing_day' => $billingDate->day]);
            $billableEntity->update(['billing_detail_id' => $billingDetails->id]);
        }

        // If the user's billing is no longer up-to-date: rebill them
        // for when the user is updating their billing info because the last bill failed
        if (!$billableEntity->subscriptionUpToDate() && $billableEntity->needsBilled()) {
            $billableEntity->bill();
        }

        // Return the billing success frame
        return view('billing.frames.pxpay-success');
    }
}
