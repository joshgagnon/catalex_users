<?php

namespace App\Http\Controllers;

use Auth;

class OrganisationInviteController extends Controller
{
    public function index()
    {
        $invites = Auth::user()->organisationInvites()->get();

        return view('organisation-invite.index')->with([
            'invites' => $invites,
        ]);
    }
}
