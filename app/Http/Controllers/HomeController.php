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
		$userId = $user->id;
		$fullName = $user->fullName();
		$timestamp = time(); // UTC
		$message = $userId . $fullName . $timestamp;

		$digest = hash_hmac('sha256', $message, env('SSO_SHARED_SECRET', null));

		$redirect = env('BROWSER_LOGIN_URL', null) . '?user_id=' . $userId . '&name=' . $fullName . '&timestamp=' . $timestamp . '&code=' . $digest;

		return redirect($redirect);
	}

	// TODO: Remove function after responsive email test
	public function getSendWelcome() {
		$user = Auth::user();
		$destination = $user->email;
		$name = $user->fullName();

		$html = view('emails.welcome', ['title' => 'Welcome', 'name' => $name])->render();
		$css = File::get(public_path('/css/email.css'));

		$inliner = new CssToInlineStyles($html, $css);
		$markup = $inliner->convert();

		Mail::send('emails.echo', ['html' => $markup], function($message) use ($destination, $name) {
			$message->to($destination, $name)->subject('Welcome to Catalex');
		});

		return redirect('/');
	}
}
