<?php

namespace App\Http\Controllers;

use App\User;
use Auth;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{

    public function startImpersonation(Request $request, User $userToBecome)
    {
        $currentUser = Auth::user();

        // Check we are a global admin and we're not trying to become a global admin
        if (!$currentUser->hasRole('global_admin') || $userToBecome->hasRole('global_admin')){
            abort(403, 'Forbidden');
        }

        // Save the admin id for when we un-become the user
        $request->session()->put('admin_id', $currentUser->id);

        // Log in as the user we want to become
        Auth::login($userToBecome);

        // Redirect home
        return redirect('/');
    }

    public function endImpersonation(Request $request)
    {
        // Check the session has an admin_id set
        if (!$request->session()->has('admin_id')) {
            abort(403, 'Forbidden');
        }

        // Login using the admin id in the session
        $adminId = $request->session()->pull('admin_id');
        Auth::loginUsingId($adminId);

        // Redirect to the users list
        $previousUser = $request->user();
        return redirect('/admin/users')->withSuccess('Logged out of ' . $previousUser->fullName());
    }
}
