<?php namespace App\Http\Controllers\Auth;

use Auth;
use Config;
use Session;
use App\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

use OAuth\ServiceFactory;
use OAuth\OAuth2\Service\GitHub;
use OAuth\Common\Storage\Session as OAuthSession;
use OAuth\Common\Http\Uri\UriFactory;
use OAuth\Common\Consumer\Credentials;

class AuthController extends Controller {

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
	}

	/**
	 * Create a new authentication controller instance.
	 *
	 * @param  \Illuminate\Contracts\Auth\Guard  $auth
	 * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
	 * @return void
	 */
	public function __construct(Guard $auth, Registrar $registrar)
	{
		$this->auth = $auth;
		$this->registrar = $registrar;

		$this->middleware('guest', ['except' => 'getLogout']);
	}

	public function postRegister(Request $request) {
		// For OAuth registrations, generate a long random password so we can
		// still use Laravel default auth (ie. for password reset)
		if(Session::get('oauth.register', false)) {
			$input = $request->input();
			$password = str_random(40);
			$request->replace($input + ['password' => $password, 'password_confirmation' => $password]);
		}

		$response = $this->defaultPostRegister($request);

		if(Auth::check()) {
			// Success, oauth flow over
			Session::forget('oauth.register');
		}

		// Give all registered users the registered_user role
		Auth::user()->addRole('registered_user');

		return $response;
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

			if(count($emails) && ($user = User::whereIn('email', $emails)->first())) {
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
				Session::put('oauth.first_name', $name);
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
			Session::put('oauth.first_name', $firstName);
			Session::put('oauth.last_name', $lastName);
			Session::put('oauth.email', $email);
			return redirect()->action('Auth\AuthController@getRegister');
		}
		else {
			return redirect($linkedIn->getAuthorizationUri()->getAbsoluteUri());
		}
	}
}
