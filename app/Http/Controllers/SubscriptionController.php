<?php

namespace App\Http\Controllers;

use App\EmailVerificationToken;
use App\Library\Mail;
use App\Service;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');

        $user = Auth::user();

        // Don't allow non-admin members of an org to try to pay
        if ($user && $user->organisation_id && !$user->can('edit_own_organisation')) {
            abort(403, 'Forbidden');
        }
    }

    /**
     * Allow the user to select the services they want to be registered to.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->organisation_id) {
            $organisation = $user->organisation;

            // Get a list of paid CataLex services
            $services = Service::where('is_paid_service', true)->get();

            // Get a list of members in this org and their subscriptions
            $members = $organisation->members()->with('services')->get();

            return view('subscriptions.org.edit')->with([
                'services' => $services,
                'members'  => $members,
            ]);
        }

        $services = Service::orderBy('is_paid_service', 'desc')->get();
        $userServices = $user->getBillableEntity()->services()->select('services.id')->get();

        foreach ($services as $service) {
            $service->userHasService = $userServices->contains($service->id);
        }

        return view('service-user.edit')->with([
            'user'     => $user,
            'services' => $services,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $membersSubscriptions = null;

        // Handle incoming data differently depending on where it is coming from (redirect from billing setup; edit org subscriptions; edit user subscriptions)
        if ($request->session()->has('redirect_data')) {
            $redirectData = $request->session()->pull('redirect_data');
            $membersSubscriptions = json_decode($redirectData['members_subscriptions'], true);
        }
        else if ($user->organisation_id) {
            $membersSubscriptions = $request->input('subscriptions', []);
        }
        else {
            $subscriptions = $request->input('services');
            $membersSubscriptions = $subscriptions ? [$user->id => $subscriptions] : []; // if this user has opted for no subscriptions, don't include them in the membersSubscriptions array
        }

        $isSubscribing = !empty($membersSubscriptions);
        $isInvoiceCustomer = $user->organisation_id && $user->organisation->is_invoice_customer;
        $needsBilling = !$isInvoiceCustomer && !$user->billingExempt();
        $needsBillingSetup = $needsBilling && !$user->hasBillingSetup();

        // Check the user has billing setup (if they need billing setup)
        if ($isSubscribing && $needsBillingSetup) {
            $request->session()->put('redirect_route_name', 'user-services.return-from-billing');
            $request->session()->put('redirect_data', ['members_subscriptions' => json_encode($membersSubscriptions)]);

            return redirect()->route('billing.register-card');
        }

        $gcService = Service::where('name', 'Good Companies')->first();
        $signService = Service::where('name', Service::SERVICE_NAME_CATALEX_SIGN)->first();
        $courtCostsService = Service::where('name', Service::SERVICE_NAME_COURT_COSTS)->first();
        $wasSubscribedToGC = $user->isSubscribedTo($gcService->id);
        $wasSubscribedToSign = $user->isSubscribedTo($signService->id);
        $wasSubscribedToCourtCosts = $user->isSubscribedTo($courtCostsService->id);

        $members = $user->organisation_id ? $user->organisation->members()->get() : [$user];

        foreach ($members as $member) {
            $subscriptionIds = [];

            // If this member has had subscriptions set in the request, add the to the subscriptions
            // Otherwise the members be will remain empty
            if (array_key_exists($member->id, $membersSubscriptions) && !empty($membersSubscriptions[$member->id])) {
                $subscriptionIds = array_keys($membersSubscriptions[$member->id]);
            }

            $member->syncSubscriptions($subscriptionIds);
        }

        $isSubscribedToGC = $user->isSubscribedTo($gcService->id);
        $isSubscribedToSign = $user->isSubscribedTo($signService->id);
        $isSubscribedToCourtCosts = $user->isSubscribedTo($signService->id);

        // If the user didn't used to be subscribed to GC, but is now: email them and thank them for subscribing.
        if (!$wasSubscribedToGC && $isSubscribedToGC) {
            Mail::queueStyledMail('emails.subscription', ['name' => $user->name], $user->email, $user->fullName(), 'Thanks for subscribing to Good Companies');
        }

        if (!$wasSubscribedToSign && $isSubscribedToSign) {
            $verifyEmailLink = null;

            if (!$user->email_verified) {
                $tokenInstance = EmailVerificationToken::createToken($user);
                $verifyEmailLink = route('email-verification.verify', $tokenInstance->token);
            }

            $billingDetail = $user->organisation_id ? $user->organisation->billing_detail : $user->billing_detail;

            $billingDate = $billingDate = $this->getBillingDate($billingDetail);

            $emailData = [
                'name' => $user->name,
                'billingDate' => $billingDate,
                'emailVerified' => $user->email_verified,
                'verifyEmailLink' => $verifyEmailLink,
            ];

            Mail::queueStyledMail('emails.subscribed-to-sign', $emailData, $user->email, $user->fullName(), 'Thanks for subscribing to CataLex Sign');
        }

        if (!$wasSubscribedToCourtCosts && $isSubscribedToCourtCosts) {
            $billingDetail = $user->organisation_id ? $user->organisation->billing_detail : $user->billing_detail;
            $billingDate = $this->getBillingDate($billingDetail);

            $emailData = [
                'name' => $user->name,
                'billingDate' => $billingDate,
            ];

            Mail::queueStyledMail('emails.subscribed-to-court-costs', $emailData, $user->email, $user->fullName(), 'Thanks for subscribing to Court Costs');
        }

        $redirectRouteName = $request->session()->has('redirect_route_name') ? $request->session()->pull('redirect_route_name') : 'user-services.index';
        return redirect()->route($redirectRouteName)->with(['success' => 'Subscriptions updated.']);
    }

    private function getBillingDate($billingDetail) {
        $billingDate = null;

        if ($billingDetail) {
            $billingDate = Carbon::createFromFormat('j', $billingDetail->billing_day);

            if ($billingDate->lt(Carbon::now())) {
                $billingDate->addMonth();
            }

            $billingDate = $billingDate->format('jS F Y');
        }

        return $billingDate;
    }
}
