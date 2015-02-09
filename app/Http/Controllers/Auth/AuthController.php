<?php namespace App\Http\Controllers\Auth;

use Auth;
use Config;
use App\User;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

use OAuth\ServiceFactory;
use OAuth\OAuth2\Service\GitHub;
use OAuth\Common\Storage\Session;
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

	use AuthenticatesAndRegistersUsers;

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

	public function getGithub()
	{
		// TODO: Abstract this all out to a service
		$serviceFactory = new ServiceFactory;

		$uriFactory = new UriFactory;
		$currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
		$currentUri->setQuery('');

		$storage = new Session;

		// Setup the credentials for the requests
		$credentials = new Credentials(
			Config::get('oauth.github.key'),
			Config::get('oauth.github.secret'),
			$currentUri->getAbsoluteUri()
		);

		// Instantiate the GitHub service using the credentials, http client and storage mechanism for the token
		/** @var $gitHub GitHub */
		$gitHub = $serviceFactory->createService('GitHub', $credentials, $storage, array('user:email'));

		if (!empty($_GET['code'])) {
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
				echo 'TODO: Show message "Can only register with verified email addresses"';
			}
			else {
				echo 'TODO: show register form with name: ' . $name . ', email: ' . $primary;
			}
		}
		else {
			// Redirect to Github login page
			return redirect($gitHub->getAuthorizationUri()->getAbsoluteUri());
		}
	}

}
