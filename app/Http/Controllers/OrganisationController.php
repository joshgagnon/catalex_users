<?php namespace App\Http\Controllers;

use Auth;
use File;
use App\Library\Invite;
use Config;
use Session;
use App\User;
use App\Organisation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\InviteFormRequest;
use App\Http\Requests\CreateOrganisationRequest;

class OrganisationController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function getIndex() {
        $user = Auth::user();

        if($user->can('view_own_organisation')) {
            $organisation = $user->organisation()->first();

            if(!$organisation) {
                return view('organisation.create');
            }

            return view('organisation.overview')->with([
                'organisation' => $organisation,
                'canEditOrganisation' => $this->canEditOrganisation($organisation, $user),
            ]);
        }

        // TODO: Error saying not enough permission
        return redirect('/');
    }

    public function postCreate(CreateOrganisationRequest $request) {
        $user = Auth::user();

        $data = $request->all();

        $organisation = Organisation::create([
            'name' => $data['organisation_name'],
            'billing_detail_id' => $user->billing_detail ? $user->billing_detail->id : null,
            'free' => false,
        ]);

        $user->addRole('organisation_admin');

        $user->organisation_id = $organisation->id;
        $user->billing_detail_id = null;
        $user->save();

        return redirect()->action('OrganisationController@getIndex');
    }

    public function postInvite(InviteFormRequest $request) {
        $data = $request->all();

        $organisation = Auth::user()->organisation;

        // Create a user for the invitee with random password
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt(str_random(40)),
            'organisation_id' => $organisation->id,
            'billing_detail_id' => null,
        ]);

        if($organisation->id == Config::get('constants.beta_organisation')) {
            $user->addRole('beta_tester');
        }
        else {
            $user->addRole('registered_user');
        }

        Invite::sendInvite($user, Auth::user()->fullName());

        Session::flash('success', 'An invite has been sent to ' . $data['email']);
        return redirect()->back();
    }

    public function edit(Organisation $organisation)
    {
        $user = Auth::user();
        if (!$this->canEditOrganisation($organisation, $user)) {
            abort(403, 'Forbidden');
        }

        return view('organisation.edit')->with([
            'organisation' => $organisation,
        ]);
    }

    public function update(Request $request, Organisation $organisation)
    {
        dd('what?');

        $user = Auth::user();
        if (!$this->canEditOrganisation($organisation, $user)) {
            dd('what?');
            abort(403, 'Forbidden');
        }

        dd('what?2');

        $this->validate($request, [
            'name' => 'required|max:255'
        ]);

        $organisation->name = $request->name;
        $organisation->save();

        return redirect('/organisation')->with('status', 'Organisation name updated');
    }

    private function canEditOrganisation(Organisation $organisation, User $user)
    {
        // Can edit any organisation
        if ($user->can('edit_any_organisation')) {
            return true;
        }

        // Can edit own organisation
        if ($user->can('edit_own_organisation')) {
            // True if the user is in the given organisation
            return $user->organisation_id == $organisation->id;
        }
        
        return false;
    }
}
