<?php namespace App\Http\Controllers;

use Auth; // TODO: Remove after demo
use App\Permission; // TODO: Remove after demo
use Illuminate\Http\Request; // TODO: Remove after demo

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
		$user = Auth::user();
		$permissions = Permission::all();
		return view('home', compact(['user', 'permissions']));
	}

	// TODO: Remove after demo
	public function permupdate(Request $request) {
		$user = Auth::user();
		$input = $request->all();

		if($input['perm'] === "Make me organisation admin") {
			if(!$user->hasRole('organisation_admin')) {
				$user->addRole('organisation_admin');
			}
		}
		if($input['perm'] === "Make me global admin") {
			if(!$user->hasRole('global_admin')) {
				$user->addRole('global_admin');
			}
		}

		$permissions = Permission::all();
		return view('home', compact(['user', 'permissions']));
	}
}
