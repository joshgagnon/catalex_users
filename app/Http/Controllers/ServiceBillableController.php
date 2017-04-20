<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Library\StringManipulation;
use App\Service;
use App\Library\Mail;

class ServiceBillableController extends Controller
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
    
    /**
     * Allow the user to select the services they want to be registered to.
     *
     * @return Response
     */
    public function index()
    {
        $user = Auth::user();
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
        // Get the services the user has set and turn it into an array of keys (the keys are the service ids)
        $newServiceIds = null;

        if ($request->services) {
            $data = !empty($request->services) ? $request->services : [];
            $newServiceIds = array_keys($data);
        } else {
            $data = json_decode($request->services_json);
            $newServiceIds = array_values($data ? : []);
        }

        // Get the user or organisation
        $user = Auth::user();
        $billableEntity = $user->organisation_id ? $user->organisation()->first() : $user;

        $servicesRequiringBilling = Service::whereIn('id', $newServiceIds)->where('is_paid_service', true)->get();

        // Check the user has billing setup (if they need billing setup)
        if (!$billableEntity->free && $servicesRequiringBilling->count() > 0 && !$billableEntity->billing_detail()->exists()) {
            $request->session()->put('redirect_route_name', 'user-services.return-from-billing');
            $request->session()->put('redirect_data', ['services_json' => json_encode($newServiceIds)]);

            return redirect()->route('billing.register-card');
        }

        // Sync the new services
        $billableEntity->services()->sync($newServiceIds);

        $goodCompanies = Service::where('name', 'Good Companies')->first();

        if ($goodCompanies && in_array($goodCompanies->id, $newServiceIds)) {
            Mail::queueStyledMail('emails.subscription', ['name' => $user->name], $user->email, $user->fullName(), 'Thanks for subscribing to Good Companies');
        }

        if ($request->session()->has('redirect_route_name')) {
            return redirect()->route($request->session()->pull('redirect_route_name'));
        }

        // Return the user to the home (services) page
        return redirect()->route('index');
    }
}
