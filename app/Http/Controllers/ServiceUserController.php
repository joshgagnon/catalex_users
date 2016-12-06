<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Service;

class ServiceUserController extends Controller
{
    /**
     * Allow the user to select the services they want to be registered to.
     *
     * @return Response
     */
    public function index()
    {
        $user = Auth::user();
        $services = Service::get();
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
        // Get the user
        $user = Auth::user();

        $servicesRequiringBilling = Service::whereIn('id', $ids)->where('is_paid_service', true)->count();

        if ($servicesRequiringBilling > 0 && !$user->hasBillingDetail()) {
            return redirect()->route('billing.confirmBilling');
        }

        // Get the services the user has set and turn it into an array of keys (the keys are the service ids)
        $data = $request->all();
        $serviceIds = array_keys(empty($data['services']) ? [] : $data['services']);

        // Sync the users services with the list of services from the request
        $user->services()->sync($serviceIds);

        // Return the user to the edit services page
        return redirect()->route('user-services.index');
    }
}
