<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class OrganisationAdminController extends Controller
{
    public function removeUser(Request $request, User $userToRemove)
    {
        $orgAdmin = $request->user();

        // Check both users (org admin and user to be removed) are in the same organisation.
        // But first check that they are both actually in an organisation
        if (
            !$orgAdmin->organisation_id
            || !$userToRemove->organisation_id
            || !$orgAdmin->hasRole('organisation_admin')
            || $orgAdmin->organisation_id !== $userToRemove->organisation_id
        ) {
            abort(403, 'Forbidden');
        }

        $userToRemove->update(['organisation_id' => null]);

        return redirect('organisation.index')->with(['success' => $userToRemove->name . ' removed from organisation.']);
    }
}
