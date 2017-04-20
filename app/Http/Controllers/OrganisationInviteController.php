<?php

namespace App\Http\Controllers;

use App\OrganisationInvite;
use Auth;
use Illuminate\Http\Request;

class OrganisationInviteController extends Controller
{
    public function index()
    {
        $invites = Auth::user()->organisationInvites()->with('organisation', 'invitedUser', 'invitingUser')->get();

        return view('organisation-invite.index')->with([
            'invites' => $invites,
        ]);
    }

    public function accept(Request $request, OrganisationInvite $invite)
    {
        // Add the user to their new organisation
        $request->user()->update([
            'organisation_id' => $invite->organisation_id
        ]);

        // Delete the invite
        $invite->delete();

        // Redirect to the users home page
        return redirect()->route('index')->with(['success' => 'Successfully joined the organisation: ' . $invite->organisation->name]);
    }

    public function dismiss(OrganisationInvite $invite)
    {
        // Delete the invite
        $invite->delete();

        // Redirect to the users home page
        return redirect()->route('index')->with(['success' => 'Organisation invite deleted.']);
    }
}
