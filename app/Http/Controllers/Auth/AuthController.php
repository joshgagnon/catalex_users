<?php namespace App\Http\Controllers\Auth;

use Config;

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
		$gitHub = $serviceFactory->createService('GitHub', $credentials, $storage, array('user'));

		if (!empty($_GET['code'])) {
			// This was a callback request from github, get the token
			$gitHub->requestAccessToken($_GET['code']);

			$result = json_decode($gitHub->request('user/emails'), true);

			echo 'The first email on your github account is ' . $result[0];

		} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
			$url = $gitHub->getAuthorizationUri();
			header('Location: ' . $url);

		} else {
			$url = $currentUri->getRelativeUri() . '?go=go';
			echo "<a href='$url'>Login with Github!</a>";
		}
	}

}
