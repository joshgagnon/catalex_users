<?php namespace App\Http\Controllers;

use Auth;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class OrganisationController extends Controller {

	public function __construct() {
		$this->middleware('auth');
	}

	public function getIndex() {
		$user = Auth::user();

		if($user->can('view_own_organisation')) {
			return view('organisation.overview', ['organisation' => $user->organisation]);
		}
		else {
			// TODO: Error saying not enough permission or not currently in org
			return redirect('/');
		}
	}
}
