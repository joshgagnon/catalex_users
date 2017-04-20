<?php namespace App\Http\Controllers;

use Auth;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use LucaDegasperi\OAuth2Server\Facades\Authorizer;
use DB;
use League\OAuth2\Server\Entity\ClientEntity;

class HomeController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Home Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders your application's "dashboard" for users that
	| are authenticated. Of course, you are free to change or remove the
	| controller as you wish. It is just here to get your app started!
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

    public function index()
    {
        $user = Auth::user();

        $subscriptionUpToDate = $user->subscriptionUpToDate();
        $userHasPendingInvite = $user->organisationInvites()->count() > 0;

        return view('home')->with([
            'subscriptionUpToDate' => $subscriptionUpToDate,
            'userHasPendingInvite' => $userHasPendingInvite,
        ]);
    }

    public function getBrowserLogin() {
        $user = Auth::user();

        if(!$user->hasBrowserAccess()) {
            return view('auth.denied');
        }
        $params = Authorizer::getAuthCodeRequestParams();
        $client = DB::table('oauth_clients')->where('name', 'Law Browser')->first();
        if(!$client) {
            return view('auth.denied');
        }
        $params['client_id'] = $client->id;
        $params['redirect_uri'] = env('BROWSER_LOGIN_URL', 'http://localhost:3000/login');
        $params['response_type'] = 'code';
        $redirect = '/login/law-browser?' . (http_build_query($params));
        return redirect($redirect);
    }

    public function getSignLogin() {
        $user = Auth::user();


        if(!$user->hasSignAccess()) {
            return view('auth.denied');
        }

        $params = Authorizer::getAuthCodeRequestParams();
        $client = DB::table('oauth_clients')->where('name', 'Sign')->first();

        if (!$client) {
            return view('auth.denied');
        }

        $params['client_id'] = $client->id;
        $params['redirect_uri'] = env('SIGN_LOGIN_URL', 'http://localhost:3000/login');
        $params['response_type'] = 'code';
        $redirect = '/login/sign?' . (http_build_query($params));
        return redirect($redirect);
    }

    public function getGoodCompaniesLogin() {
        $user = Auth::user();

        if(!$user->hasGoodCompaniesAccess()) {
            return view('auth.denied');
        }

        if (!$user->subscriptionUpToDate()) {
            return redirect()->route('index');
        }

        $params = Authorizer::getAuthCodeRequestParams();
        $client = DB::table('oauth_clients')->where('name', 'Good Companies')->first();
        if(!$client) {
            return view('auth.denied');
        }
        $params['client_id'] = $client->id;
        $params['redirect_uri'] = env('GOOD_COMPANIES_LOGIN_URL', 'http://localhost:5667/auth/catalex/login');
        $params['response_type'] = 'code';
        $redirect = '/login/good-companies?' . (http_build_query($params));
        return redirect($redirect);
    }

}

