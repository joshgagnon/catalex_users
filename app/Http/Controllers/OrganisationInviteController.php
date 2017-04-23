<?php

namespace App\Http\Controllers;

use App\OrganisationInvite;
use Auth;
use Illuminate\Http\Request;

class OrganisationInviteController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->organisation) {
            redirect()->back()->withErrors('Can\'t access organisation invites. You are already in an organisation.');
        }

        $invites = $user->organisationInvites()->with('organisation', 'invitedUser', 'invitingUser')->get();

        return view('organisation-invite.index')->with([
            'invites' => $invites,
        ]);
    }

    public function accept(Request $request, OrganisationInvite $invite)
    {
        $user = Auth::user();

        // Don't allow user to accept invites if they are already in an organisation
        if ($user->organisation) {
            redirect()->back()->withErrors('Can\'t access organisation invites. You are already in an organisation.');
        }

        // Check invite belongs to the current user - if not, deny access
        if ($user->id !== $invite->invited_user_id) {
            return view('auth.denied');
        }

        // Add the user to their new organisation
        $request->user()->update(['organisation_id' => $invite->organisation_id]);

        // Delete the invite
        $invite->delete();

        // Redirect to the users home page
        return redirect()->route('index')->with(['success' => 'Successfully joined the organisation: ' . $invite->organisation->name]);
    }

    public function dismiss(OrganisationInvite $invite)
    {
        $user = Auth::user();

        // Check invite belongs to or was created by the current user - if not, deny access
        if ($user->id !== $invite->invited_user_id && $user->id !== $invite->inviting_user_id) {
            return view('auth.denied');
        }

        // Delete the invite
        $invite->delete();

        // Redirect to the users home page
        return redirect()->route('index')->with(['success' => 'Organisation invite deleted.']);
    }
}
