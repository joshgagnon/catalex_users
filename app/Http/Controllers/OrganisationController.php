<?php namespace App\Http\Controllers;

use Auth;
use Session;
use App\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\InviteFormRequest;
use Illuminate\Contracts\Auth\PasswordBroker;

class OrganisationController extends Controller {

	/**
	 * The password broker implementation.
	 *
	 * @var PasswordBroker
	 */
	protected $passwordBroker;

	public function __construct(PasswordBroker $passwordBroker) {
		$this->passwordBroker = $passwordBroker;

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

	public function postInvite(InviteFormRequest $request) {
		$data = $request->all();

		//return redirect()->with('success', 'Awesome')->back();

		$organisation = Auth::user()->organisation;

		// Create a user for the invitee with random password
		$user = User::create([
			'first_name' => $data['first_name'],
			'last_name' => $data['last_name'],
			'email' => $data['email'],
			'password' => bcrypt(str_random(40)),
			'organisation_id' => $organisation->id,
			'billing_detail_id' => null,
		]);

		$user->addRole('registered_user');

		// Send out invite to allow user to log in
		// TODO: Template should say 'you can create password at <x link> or login <here> with linkedIn
		$response = $this->passwordBroker->sendResetLink(['email' => $data['email']], function($mail) {
			$mail->subject('Welcome to Catalex');
		});

		Session::flash('success', 'An invite has been sent to ' . $data['email']);
		return redirect()->back();
	}
}
