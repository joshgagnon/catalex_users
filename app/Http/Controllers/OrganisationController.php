<?php namespace App\Http\Controllers;

use Auth;
use File;
use App\Library\Invite;
use Config;
use Session;
use App\User;
use App\Organisation;
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
            $organisation = $user->organisation;

            if(!$organisation) {
                return view('organisation.create');
            }

            return view('organisation.overview', ['organisation' => $organisation]);
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

        // Attach the user's services to the org
        $userServices = User::find(177)->services()->get()->pluck('id')->toArray();
        $organisation->services()->attach($userServices);

        // Give the user the role: org admin
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
}
