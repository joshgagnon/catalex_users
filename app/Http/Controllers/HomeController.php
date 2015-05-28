<?php namespace App\Http\Controllers;

use Auth;
use Mail; // TODO: Remove
use File; // TODO: Remove
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

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

	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function index()
	{
		return view('home');
	}

	public function getBrowserLogin() {
		$user = Auth::user();

		if(!$user->hasBrowserAccess()) {
			return view('auth.denied');
		}

		$userId = $user->id;
		$fullName = $user->fullName();
		$timestamp = time(); // UTC
		$admin = $user->hasRole('admin') ? 'true' : 'false';
		$message = env('BROWSER_LOGIN_URL', null) . $userId . $fullName . $timestamp . $admin;

		$digest = hash_hmac('sha256', $message, env('SSO_SHARED_SECRET', null));

		$redirect = env('BROWSER_LOGIN_URL', null) . '?user_id=' . $userId . '&name=' . $fullName . '&timestamp=' . $timestamp  . '&admin=' . $admin. '&code=' . $digest;
		return redirect($redirect);
	}
}
