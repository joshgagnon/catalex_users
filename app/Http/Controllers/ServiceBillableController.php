<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Library\StringManipulation;
use App\Service;

class ServiceBillableController extends Controller
{
    /**
     * Allow the user to select the services they want to be registered to.
     *
     * @return Response
     */
    public function index()
    {
        $user = Auth::user();
        $services = Service::orderBy('is_paid_service', 'desc')->get();
        $userServices = $user->services()->select('services.id')->get();

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
        $newServiceIds;

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
            $request->session()->put('billing_initial_setup', true);
            $request->session()->put('redirect_route_name', 'user-services.return-from-billing');
            $request->session()->put('redirect_data', ['services_json' => json_encode($newServiceIds)]);

            return redirect()->route('billing.select-period');
        }

        // Sync the new services
        $billableEntity->services()->sync($newServiceIds);

        if ($request->session()->has('redirect_route_name')) {
            return redirect()->route($request->session()->pull('redirect_route_name'));
        }

        // Return the user to the home (services) page
        return redirect()->route('index');
    }
}
