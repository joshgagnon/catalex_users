<?php namespace App\Services;

use Config;
use App\User;
use App\Address;
use Carbon\Carbon;
use App\Library\Mail;
use App\Organisation;
use App\BillingDetail;

use Session;
use Validator;
use Illuminate\Contracts\Auth\Registrar as RegistrarContract;

class Registrar implements RegistrarContract {

	/**
	 * Get a validator for an incoming registration request.
	 *
	 * @param  array  $data
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
	public function validator(array $data) {
		return Validator::make($data, [
			'name' => 'required|max:255',
			'email' => 'required|email|max:255|unique:users',
			'password' => 'required|confirmed|min:6',
			'business_name' => 'max:255',
			'customer_agreement' => 'accepted',
		]);
	}

	/**
	 * Create a new user and optionally an organisation for non-invite registrations
	 *
	 * @param  array  $data
	 * @return User
	 */
	public function create(array $data) {
		$organisation = null;

		$user = User::create([
			'name' => $data['name'],
			'email' => $data['email'],
			'password' => bcrypt($data['password']),
			// User should belong to organisation of be billed directly, not both
			'organisation_id' => $organisation ? $organisation->id : null,
			//'billing_detail_id' => $organisation ? null : $billing->id,
		]);

		// Add basic roles for the user
		$user->addRole('registered_user');
		// And org roles if registering as an organistaion - it's assumed the first user is an admin
		if($organisation) {
			$user->addRole('organisation_admin');
		}

		// Send out welcome email
		$trialEnd = Carbon::now()->addMinutes(Config::get('constants.trial_length_minutes'));
		Mail::queueStyledMail('emails.welcome', [
			'name' => $user->fullName(),
			'email' => $user->email,
			//'trialEnd' => $trialEnd->format('F j'),
		], $user->email, $user->fullName(), 'Welcome to CataLex');

		return $user;
	}

}
