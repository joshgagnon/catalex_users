<?php namespace App\Http\Controllers;

use Auth;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Library\PXPay;
use App\Library\Billing;
use App\BillingDetail;
use App\User;
use App\Organisation;

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
        if($user && $user->organisation && !$user->can('edit_own_organisation')) {
            abort(403, 'Forbidden');
        }
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

    public function createCard(Request $request)
    {
        // Make sure we have sent the user here (they aren't just hitting the route)
        if (!$request->session()->has('redirect_route_name')) {
            abort(403, 'Forbidden');
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
            'gatewayURL' => $response->getRedirectUrl(),
        ]);
    }

    public function finishCreateCard(Request $request)
    {
        // Make sure we have sent the user here (they aren't just hitting the route)
        if (!$request->session()->has('redirect_route_name')) {
            abort(403, 'Forbidden');
        }

        $routeName = $request->session()->pull('redirect_route_name');
        $data = $request->session()->has('redirect_data') ? $request->session()->pull('redirect_data') : [];

        return redirect()->route($routeName, $data);
    }

    public function storeCard(Request $request)
    {
        // Use result query param to get real auth result data
        $gateway = PXPay::getGateway();
        $response = $gateway->completeCreateCard()->send();
        $responseData = $response->getData();

        if (boolval((string)$responseData->Success) == false) {
            return view('billing.frames.pxpay-failed');
        }

        $billableEntity = Auth::user();
        $billableEntity = $billableEntity->organisation_id ? $billableEntity->organisation()->first() : $billableEntity;
        $billingDetails = $billableEntity->billing_detail()->first();

        $billingToken = (string)$responseData->DpsBillingId;
        $expiryDate = (string)$responseData->DateExpiry;

        if (!$billingDetails) {
            // This user or organisation hasn't already got billing details setup; create there billing details
            $billingDetails = new BillingDetail();

            $billingDetails->period = 'annually'; // Default to annual payments
            $billingDetails->billing_day = Carbon::today()->addDays(Billing::DAYS_IN_TRIAL_PERIOD)->day;
            $billingDetails->dps_billing_token = $billingToken;
            $billingDetails->expiry_date = $expiryDate;
            $billingDetails->save();

            // Link the newly created billing details to the user or organisation
            $billableEntity->billing_detail_id = $billingDetails->id;
            $billableEntity->save();
        } else {
            // Update billing details
            $billingDetails->dps_billing_token = $billingToken;
            $billingDetails->expiry_date = $expiryDate;
            $billingDetails->save();
        }

        return view('billing.frames.pxpay-success');
    }
}
