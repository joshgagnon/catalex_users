<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Service;
use App\Library\Mail;

class SubscriptionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct() {
        $this->middleware('auth');

        $user = Auth::user();

        // Don't allow non-admin members of an org to try to pay
        if($user && $user->organisation && !$user->can('edit_own_organisation')) {
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
                'members' => $members,
            ]);
        }

        $services = Service::orderBy('is_paid_service', 'desc')->get();
        $userServices = $user->getBillableEntity()->services()->select('services.id')->get();

        foreach ($services as $service) {
            $service->userHasService = $userServices->contains($service->id);
        }

        return view('service-user.edit')->with([
            'user' => $user,
            'services' => $services,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        // We handle users in orgs differently
        if ($user->organisation_id) {
            $organisation = $user->organisation;
            $members = $organisation->members()->get();

            $membersSubscriptions = $request->input('subscriptions');

            if (!$membersSubscriptions) {
                $membersSubscriptions = [];
            }

            foreach ($members as $member) {
                $subscriptionIds = [];

                // If this member has had subscriptions set in the request, add the to the subscriptions
                // Otherwise the members be will remain empty
                if (array_key_exists($member->id, $membersSubscriptions)) {
                    $subscriptionIds = array_keys($membersSubscriptions[$member->id]);
                }

                $member->services()->sync($subscriptionIds);
            }
        }
        else {
            // Get the services the user has set and turn it into an array of keys (the keys are the service ids)
            $newServiceIds = null;

            if ($request->services) {
                $data = !empty($request->services) ? $request->services : [];
                $newServiceIds = array_keys($data);
            } else {
                $data = json_decode($request->services_json);
                $newServiceIds = array_values($data ?: []);
            }

            // Get the user or organisation
            $servicesRequiringBilling = Service::whereIn('id', $newServiceIds)->where('is_paid_service', true)->get();

            // Check the user has billing setup (if they need billing setup)
            if (!$user->free && $servicesRequiringBilling->count() > 0 && !$user->billing_detail_id) {
                $request->session()->put('redirect_route_name', 'user-services.return-from-billing');
                $request->session()->put('redirect_data', ['services_json' => json_encode($newServiceIds)]);

                return redirect()->route('billing.register-card');
            }

            // Sync the new services
            $user->services()->sync($newServiceIds);

            $goodCompanies = Service::where('name', 'Good Companies')->first();

            if ($goodCompanies && in_array($goodCompanies->id, $newServiceIds)) {
                Mail::queueStyledMail('emails.subscription', ['name' => $user->name], $user->email, $user->fullName(), 'Thanks for subscribing to Good Companies');
            }
        }

        $redirectRouteName = $request->session()->has('redirect_route_name') ? $request->session()->pull('redirect_route_name') : 'index';
        return redirect()->route($redirectRouteName)->with(['success' => 'Subscriptions updated']);
    }
}
