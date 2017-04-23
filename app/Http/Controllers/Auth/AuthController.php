<?php

namespace App\Http\Controllers\Auth;

use Auth;
use Config;
use Session;
use App\Library\Mail;
use App\Library\PXPay;
use App\User;
use App\Organisation;
use Validator;
use App\Http\Requests\InitialRegisterRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

use OAuth\ServiceFactory;
use OAuth\OAuth2\Service\GitHub;
use OAuth\Common\Storage\Session as OAuthSession;
use OAuth\Common\Http\Uri\UriFactory;
use OAuth\Common\Consumer\Credentials;

use Omnipay\Omnipay;

class AuthController extends Controller {

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

    /**
     * Create a new authentication controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\Guard  $auth
     * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    public function validator(array $data) {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
           // 'business_name' => 'max:255',
            'customer_agreement' => 'accepted',
        ]);
    }

    /**
     * Create a new user and optionally an organisation for non-invite registrations
     *
     * @param  array  $data
     * @return User
     */
    public function create(array $data) {
        $organisation = null;

        /*if(strlen(trim($data['business_name']))) {
            $organisation = Organisation::create([
                'name' => $data['business_name'],
                //'billing_detail_id' => $billing->id,
                'free' => true,
            ]);
        }*/

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            // User should belong to organisation of be billed directly, not both
            //'organisation_id' => $organisation ? $organisation->id : null,
            //'billing_detail_id' => $organisation ? null : $billing->id,
        ]);

        // Add basic roles for the user
        $user->addRole('registered_user');
        // And org roles if registering as an organistaion - it's assumed the first user is an admin
       /*if($organisation) {
            $user->addRole('organisation_admin');
        }*/

        // Send out welcome email
        $trialEnd = Carbon::now()->addMinutes(Config::get('constants.trial_length_minutes'));
        Mail::queueStyledMail('emails.welcome', [
            'name' => $user->fullName(),
            'email' => $user->email,
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
        $registerToGoodCompanies = $request->has('gc');

        // Fold previous step post data into this request
        //$request->replace($request->input() + Session::get('register.personal'));
        $request->replace($request->input());

        if($registerToGoodCompanies){
            $this->redirectTo = route('user-services.index', array(urlencode('Good Companies') => 1));
        }

        // For OAuth registrations, generate a long random password so we can
        // still use Laravel default auth (ie. for password reset)
        if(Session::get('oauth.register', false)) {
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

    public function redirectPath() {
        return $this->defaultRedirectPath();
    }

    public function postBilling(InitialRegisterRequest $request) {
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

        if(!empty($_GET['code'])) {
            // This was a callback request from github, get the token
            $gitHub->requestAccessToken($_GET['code']);

            // Just log in if we find a recognized and verified email
            $result = json_decode($gitHub->request('/user/emails'));
            $emails = [];
            $primary = null;

            foreach($result as $emailDetails) {
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

            if(!$primary) {
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

        $linkedIn = $serviceFactory->createService('linkedin', $credentials, $storage, ['r_basicprofile', 'r_emailaddress']);

        if(!empty($_GET['code'])) {
            // retrieve the CSRF state parameter
            $state = isset($_GET['state']) ? $_GET['state'] : null;

            // This was a callback request from linkedin, get the token
            $token = $linkedIn->requestAccessToken($_GET['code'], $state);

            $result = json_decode($linkedIn->request('/people/~?format=json'));
            $firstName = $result->firstName;
            $lastName = $result->lastName;

            $result = json_decode($linkedIn->request('/people/~/email-address?format=json'));
            $email = $result;

            if($email && ($user = User::where('email', $email)->first())) {
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
