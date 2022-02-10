<?php

namespace App\Http\Controllers;

use App\CardDetail;
use App\InvoiceRecipient;
use App\Library\BillingItemSummariser;
use App\Trial;
use Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Library\PXPay;
use App\BillingDetail;

class BillingController extends Controller
{
    /**
     * Create a new controller instance.
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

        $discountPercent = null;
        
        if ($billable->billing_detail && $billable->billing_detail->discount_percent) {
            $discountPercent = $billable->billing_detail->discount_percent;
        }
        
        return view('billing.index')->with([
            'skipBilling' => $billable->skip_billing,
            'subscriptionUpToDate' => $subscriptionUpToDate,
            'chargeLogs' => $chargeLogs,
            'billingItems' => $billingItems,
            $billableKeyName => $billable,
            'discountPercent' => $discountPercent,
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

    public function edit(Request $request)
    {
        $user = $request->user();
        $billableEntity = $user->organisation ? $user->organisation : $user;
        $billingDetails = $billableEntity->billing_detail()->first();
        $cardDetails = $billingDetails ? $billingDetails->cardDetail ()->first() : null;

        $subscriptionUpToDate = $user->subscriptionUpToDate();

        return view('billing.edit')->with([
            'billingDetails' => $billingDetails,
            'subscriptionUpToDate' => $subscriptionUpToDate,
            'cardDetails' => $cardDetails,
            'is_invoice_customer' => $billableEntity->is_invoice_customer,
            'skip_billing' => $billableEntity->skip_billing
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

    public function delete(Request $request)
    {
        // Get the billing details
        $billableEntity = $request->user()->getBillableEntity();
        $billingDetails = $billableEntity->billing_detail()->first();

        // Delete the card
        $billingDetails->update(['card_detail_id' => null]);
        $billingDetails->cardDetail()->delete();

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
        $billableEntity = $request->user()->getBillableEntity();

        // Get the card details
        $billingDetails = $billableEntity->billing_detail()->first();
        $cardDetails = $billingDetails ? $billingDetails->cardDetail()->first() : null;

        if ($cardDetails) {
            return redirect()->route('billing.edit')->withErrors('You already have a card setup, please remove it to add a new one.');
        }

        // Create a new payment gateway request to get iframe url to show
        $gateway = PXPay::getGateway();

        // Start the request to DPS
        $response = $gateway->createCard([
            'returnUrl' => route('billing.store-card'),
            'currency' => PXPay::CURRENCY_NZD,
        ])->send();

        if (!$response->isRedirect()) {
            return redirect()->back()->withErrors(['An error occurred contacting the payment gateway, please try again.']);
        }

        $gatewayUrl = $response->getRedirectUrl();

        return view('billing.preamble')->with(['gatewayURL' => $gatewayUrl]);
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
            return redirect()->route($routeName);
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

        $billableEntity = $request->user()->getBillableEntity();
        $billingDetails = $billableEntity->billing_detail()->first();

        $cardDetail = CardDetail::create([
            'card_token' => (string)$responseData->DpsBillingId,
            'expiry_date' => (string)$responseData->DateExpiry,
            'masked_card_number' => (string)$responseData->CardNumber,
        ]);

        if ($billingDetails) {
            try {
                $billingDetails->cardDetail()->delete();
            } catch(\Exception $e) {

            }
            $billingDetails->update(['card_detail_id' => $cardDetail->id]);
        }
        else {
            $trial = Trial::findOrCreate($billableEntity, 'Good Companies');
            $billingDate = Carbon::now()->lte($trial->end_date) ? $trial->end_date->addDays(1) : Carbon::today();

            $period = $request->session()->has('billing_period') ? $request->session()->pull('billing_period') : 'monthly'; // Default: monthly

            $billingDetails = BillingDetail::create([
                'period' => $period,
                'billing_day' => $billingDate->day,
                'card_detail_id' => $cardDetail->id,
            ]);

            $billableEntity->update(['billing_detail_id' => $billingDetails->id]);
        }

        // If the user's billing is no longer up-to-date: rebill them
        // for when the user is updating their billing info because the last bill failed
        if (!$billableEntity->subscriptionUpToDate() && $billableEntity->needsBilled()) {
            $billableEntity->bill();
        }

        // Return the billing success frame
        return view('billing.finalize')->withSuccess('Card successfully added');
    }
}
