<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ResetBroker as PasswordBroker;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Library\Invite;

class PasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    protected $redirectTo = '/';

    /**
     * Create a new password controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\Guard  $auth
     * @param  \Illuminate\Contracts\Auth\PasswordBroker  $passwords
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function getFirstLogin($token = null)
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

    public function postFirstLogin(Request $request)
    {
        // Validate
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
