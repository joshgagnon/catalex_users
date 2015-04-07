<?php namespace App\Http\Controllers;

use Auth;
use File;
use Mail;
use Session;
use App\User;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Http\Requests\InviteFormRequest;
use Illuminate\Contracts\Auth\PasswordBroker;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

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

		$organisation = Auth::user()->organisation;

		$tempPassword = Str::quickRandom(8);

		// Create a user for the invitee with random password
		$user = User::create([
			'first_name' => $data['first_name'],
			'last_name' => $data['last_name'],
			'email' => $data['email'],
			'password' => bcrypt($tempPassword),
			'organisation_id' => $organisation->id,
			'billing_detail_id' => null,
		]);

		$user->addRole('registered_user');

		// Send out invite to allow user to log in
		// TODO: Template should say 'you can create password at <x link> or login <here> with linkedIn
		/*$response = $this->passwordBroker->sendResetLink(['email' => $data['email']], function($mail) {
			$mail->subject('Welcome to CataLex');
		});*/

		// TODO: functionise css inlining for mail
		$destination = $user->email;
		$name = $user->fullName();
		$loginURL = action('UserController@getProfile');

		$html = view('emails.invite', compact(['name', 'tempPassword', 'loginURL']))->render();
		$css = File::get(public_path('/css/email.css'));

		$inliner = new CssToInlineStyles($html, $css);
		$markup = $inliner->convert();

		Mail::send('emails.echo', ['html' => $markup], function($message) use ($destination, $name) {
			$message->to($destination, $name)->subject('Welcome to CataLex');
		});

		Session::flash('success', 'An invite has been sent to ' . $data['email']);
		return redirect()->back();
	}
}
