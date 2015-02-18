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
			$organisation = $user->organisation;

			if(!$organisation) {
				// TODO: Redirect to page offering upgrade to organisation
				return redirect ('/');
			}

			return view('organisation.overview', ['organisation' => $organisation]);
		}

		// TODO: Error saying not enough permission
		return redirect('/');
	}
}
