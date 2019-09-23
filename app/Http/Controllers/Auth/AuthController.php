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
        if ($request->next) {
            return redirect($request->next);
        }

        // Old style redirect
        if ($request->has('redirectToSign')) {
            return redirect()->route('sign-login');
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

        return redirect($this->defaultRedirectPath());
    }


    public function validator(array $data)
    {
        return Validator::make($data, [
            'name'               => 'required|max:255',
            'email'              => 'required|email|max:255|unique:users',
            'password'           => 'required|confirmed|min:6',
            'customer_agreement' => 'accepted',
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

    public function getGithub()
    {
        // TODO: Abstract this all out to a service
        $serviceFactory = new ServiceFactory;

        $uriFactory = new UriFactory;
        $currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
        $currentUri->setQuery('');

        $storage = new OAuthSession;

        // Setup the credentials for the requests
        $credentials = new Credentials(
            Config::get('oauth.github.key'),
            Config::get('oauth.github.secret'),
            $currentUri->getAbsoluteUri()
        );

        // Instantiate the GitHub service using the credentials, http client and storage mechanism for the token
        /** @var $gitHub GitHub */
        $gitHub = $serviceFactory->createService('GitHub', $credentials, $storage, ['user:email']);

        if (!empty($_GET['code'])) {
            // This was a callback request from github, get the token
            $gitHub->requestAccessToken($_GET['code']);

            // Just log in if we find a recognized and verified email
            $result = json_decode($gitHub->request('/user/emails'));
            $emails = [];
            $primary = null;

            foreach ($result as $emailDetails) {
                // TODO: If github is ever wanted, figure out how to get verified emails
                //       Apparently the documentation is lying about this
                /*
                if($emailDetails->verified) {
                    $emails[] = $emailDetails->email;
                    if($emailDetails->primary) {
                        $primary = $emailDetails->email;
                    }
                }
                */
                $primary = $emailDetails;
                $emails[] = $emailDetails;
            }

            if (count($emails) && ($user = User::whereIn('email', $emails)->first())) {
                Auth::login($user);
                return redirect()->action('HomeController@index');
            }

            $result = json_decode($gitHub->request('user'));
            $name = $result->name;

            if (!$primary) {
                // TODO: Update to show error when correctly determining if email is verified above
            }
            else {
                Session::put('oauth.register', true);
                Session::put('oauth.name', $name);
                Session::put('oauth.email', $primary);
                return redirect()->action('Auth\AuthController@getRegister');
            }
        }
        else {
            // Redirect to Github login page
            return redirect($gitHub->getAuthorizationUri()->getAbsoluteUri());
        }
    }

    public function getLinkedin()
    {
        $serviceFactory = new ServiceFactory;

        $uriFactory = new UriFactory;
        $currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
        $currentUri->setQuery('');

        $storage = new OAuthSession;

        $credentials = new Credentials(
            Config::get('oauth.linkedin.key'),
            Config::get('oauth.linkedin.secret'),
            $currentUri->getAbsoluteUri()
        );

        $linkedIn = $serviceFactory->createService('linkedin', $credentials, $storage, ['r_liteprofile', 'r_emailaddress']);

        if (!empty($_GET['code'])) {
            // retrieve the CSRF state parameter
            $state = isset($_GET['state']) ? $_GET['state'] : null;

            // This was a callback request from linkedin, get the token
            $token = $linkedIn->requestAccessToken($_GET['code'], $state);

            $result = json_decode($linkedIn->request('/people/~?format=json'));
            $firstName = $result->firstName;

            $result = json_decode($linkedIn->request('/people/~/email-address?format=json'));
            $email = $result;

            if ($email && ($user = User::where('email', $email)->first())) {
                Auth::login($user);
                return redirect()->action('HomeController@index');
            }

            Session::put('oauth.register', true);
            Session::put('oauth.name', $firstName);
            Session::put('oauth.email', $email);
            return redirect()->action('Auth\AuthController@getRegister');
        }
        else {
            return redirect($linkedIn->getAuthorizationUri()->getAbsoluteUri());
        }
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

        return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/');
    }
}
