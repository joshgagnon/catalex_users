<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Library\Invite;
use Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FirstLoginController extends Controller
{
    public function index($token = null)
    {
        if (!$token) {
            throw new NotFoundHttpException();
        }

        // Get the user
        $user = Invite::getUser($token);

        if (!$user) {
            throw new NotFoundHttpException();
        }

        $userOrganisation = $user->organisation;

        return view('auth.first-login')->with([
            'token' => $token,
            'user' => $user,
            'userOrganisation' => $userOrganisation
        ]);
    }

    public function setPassword(Request $request)
    {
        // Validate token and password exist and password is confirmed
        $this->validate($request, [
            'token' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);

        // Get the user for this token
        $user = Invite::getUser($request->token);

        // Check we found a user
        if (!$user) {
            return redirect()->back()->with('errors', collect('Invalid token'));
        }

        // Change the user's password
        $user->password = \Hash::make($request->password);
        $user->save();

        // Delete the token - it's a single use deal
        $user->firstLoginToken()->delete();

        // Log the user in
        Auth::login($user);

        // Done. Redirect into app
        return redirect('/')->with('status', 'Password set');
    }
}
