<?php

namespace App\Http\Controllers;
use App\Http\Requests\Organisation2faRequest;
use Illuminate\Http\Request;
use App\Http\Requests\CreateOrganisationRequest;
use App\Http\Requests\InviteFormRequest;
use App\Library\Invite;
use App\Library\Mail;
use App\Organisation;
use App\OrganisationInvite;
use App\User;
use Auth;
use Config;
use File;
use Session;

class OrganisationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->can('view_own_organisation')) {
            $organisation = $user->organisation;

            if (!$organisation) {
                return view('organisation.create');
            }

            return view('organisation.overview', ['organisation' => $organisation]);
        }

        // TODO: Error saying not enough permission
        return redirect('/');
    }

    public function postCreate(CreateOrganisationRequest $request)
    {
        $user = Auth::user();
        $data = $request->all();

        $organisation = Organisation::create([
            'name'              => $data['organisation_name'],
            'billing_detail_id' => $user->billing_detail ? $user->billing_detail->id : null,
            'free'              => false,
        ]);

        // Give the user the role: org admin
        $user->addRole('organisation_admin');

        $organisation->invoiceRecipients()->create([
            'name'  => $user->name,
            'email' => $user->email,
        ]);

        $user->organisation_id = $organisation->id;
        $user->billing_detail_id = null;
        $user->save();

        return redirect()->route('organisation.index');
    }

    public function postInvite(InviteFormRequest $request)
    {
        $inviter = $request->user();
        $inviter->load('organisation');

        $data = $request->all();
        $invitee = User::where('email', $data['email'])->first();

        if ($invitee) {
            if ($invitee->organisation_id) {
                redirect()->back()->withErrors('User with email: ' . $data['email'] . ' already belongs to an organisation.');
            }

            // Send invite
            OrganisationInvite::create([
                'invited_user_id'  => $invitee->id,
                'inviting_user_id' => $inviter->id,
                'organisation_id'  => $inviter->organisation->id,
            ]);

            $inviteEmailData = [
                'name'         => $invitee->name,
                'inviter'      => $inviter->name,
                'organisation' => $inviter->organisation->name,
            ];

            Mail::queueStyledMail('emails.join-organisation', $inviteEmailData, $invitee->email, $invitee->name, 'You have been invited to join a CataLex organisation');
        }
        else {
            // User doesn't exist - so create one
            // Create a user for the invitee with random password
            $invitee = User::create([
                'name'              => $data['name'],
                'email'             => $data['email'],
                'password'          => bcrypt(str_random(40)),
                'billing_detail_id' => null,
            ]);

            $invitee->addRole('registered_user');

            $organisation = $inviter->organisation;
            $organisation->join($invitee);

            Invite::sendInvite($invitee, $inviter->fullName());
        }

        return redirect()->back()->with(['success' => 'An invite has been sent to ' . $data['email']]);
    }

    public function postEdit2fa(Organisation2faRequest $request)
    {
        $user = Auth::user();

        $organisation = $user->organisation;
        $organisation->require_2fa = (bool)$request->input()['require_2fa'];

        $organisation->save();
        return redirect()->action('OrganisationController@index');
    }
}
