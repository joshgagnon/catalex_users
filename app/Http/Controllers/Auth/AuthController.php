<?php

namespace App\Http\Controllers\Auth;

use App\EmailVerificationToken;
use App\Http\Controllers\Controller;
use App\Http\Requests\InitialRegisterRequest;
use App\Library\Mail;
use App\User;
use Auth;
use Config;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Uri\UriFactory;
use OAuth\Common\Storage\Session as OAuthSession;
use OAuth\OAuth2\Service\GitHub;
use OAuth\ServiceFactory;
use Session;
use Validator;

class AuthController extends Controller
{

    protected $redirectTo = '/';

    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers {
        postRegister as defaultPostRegister;
        redirectPath as defaultRedirectPath;
    }

    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    protected function authenticated(Request $request)
    {
        // Redirect to given path - maybe we should only allow *.catalex.nz/*


        // Old style redirect
        if ($request->has('redirectToSign')) {
            return redirect()->route('sign-login');
        }

        if ($request->product && $request->next) {

            if ($request->product === 'gc') {
                return redirect()->route('good-companies-login', ['next' => $request->next]);
            }

            if ($request->product === 'sign') {
                return redirect()->route('sign-login', ['next' => $request->next]);
            }

            if ($request->product === 'cc') {
                return redirect()->route('cc-login', ['next' => $request->next]);
            }
        }
        // New style redirect - use this if just redirecting to home page of a service.

        if ($request->product) {
            if ($request->product === 'gc') {
                return redirect()->route('good-companies-login');
            }

            if ($request->product === 'sign') {
                return redirect()->route('sign-login');
            }

            if ($request->product === 'cc') {
                return redirect()->route('cc-login');
            }

            if ($request->product === 'browser') {
                return redirect()->route('browser-login');
            }

        }

        if ($request->next) {
            return redirect($request->next);
        }

        return redirect($this->defaultRedirectPath());
    }


    public function validator(array $data)
    {
        return Validator::make($data, [
            'name'               => 'required|max:255',
            'email'              => 'required|email|max:255|unique:users',
            'password'           => 'required|confirmed|min:6',
            'customer_agreement' => 'accepted',
           # 'captcha'            => 'required|captcha'
        ]);
    }

    /**
     * Create a new user and optionally an organisation for non-invite registrations
     *
     * @param  array $data
     * @return User
     */
    public function create(array $data)
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        // Add basic roles for the user
        $user->addRole('registered_user');

        // Send out welcome email
        $tokenInstance = EmailVerificationToken::createToken($user);
        Mail::queueStyledMail('emails.welcome', [
            'name'              => $user->fullName(),
            'email'             => $user->email,
            'verification_link' => route('email-verification.verify', $tokenInstance->token),
        ], $user->email, $user->fullName(), 'Welcome to CataLex');

        return $user;
    }

    public function getRegister(Request $request)
    {
        // Grab the query string and pass it along to the view
        // The view will want this to include in the post
        $queryParams = $request->query();
        $queryString = http_build_query($queryParams);

        return view('auth.register')->with(['queryString' => $queryString]);
    }

    public function postRegister(Request $request)
    {

        $user = User::where('email', 'ilike', $request->input('email'))->first();
        if ($user) {
            if ($user->is_shadow_user) {
                return redirect()->back()->with('errors', collect('If this is your email address, please go to Login -> Reset Password to complete your account setup.'));
            }

            return redirect()->back()->with('errors', collect('The email has already been taken'));
        }

        $registerToGoodCompanies = $request->has('gc');
        $registerToSign = $request->has('sign');

        $redirectToSign = $request->has('redirectToSign');

        // Fold previous step post data into this request
        //$request->replace($request->input() + Session::get('register.personal'));
        $request->replace($request->input());

        if ($registerToGoodCompanies) {
            $this->redirectTo = route('user-services.index', [urlencode('Good Companies') => 1]);
        }

        if ($registerToSign) {
            $this->redirectTo = route('user-services.index', [urlencode('CataLex Sign') => 1]);
        }

        if ($redirectToSign) {
            $this->redirectTo = route('sign-login');
        }

        // For OAuth registrations, generate a long random password so we can
        // still use Laravel default auth (ie. for password reset)
        if (Session::get('oauth.register', false)) {
            $input = $request->input();
            $password = str_random(40);
            $request->replace($input + ['password' => $password, 'password_confirmation' => $password]);
        }

        // Hand over to internal Laravel user setup
        $response = $this->defaultPostRegister($request);

        if (Auth::check()) {
            // Success, oauth flow over
            Session::forget('oauth.register');
        }

        return $response;
    }

    public function redirectPath()
    {
        return $this->defaultRedirectPath();
    }

    public function postBilling(InitialRegisterRequest $request)
    {
        // Store current progress in Session and show form for billing step
        Session::put('register.personal', $request->except(['_token']));

        // Show from get rather than post so that users can be redirected back to form on validation errors
        return redirect()->action('Auth\AuthController@getBilling');
    }


    /**
     * Override logout to remove the session value we use for impersonation
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function getLogout(Request $request)
    {
        // Forget the admin_id (id there is one)
        $request->session()->forget('admin_id');

        // Logout
        Auth::logout();
        Session::flush();
        return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/');
    }
}
