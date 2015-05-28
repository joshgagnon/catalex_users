<?php namespace App\Services;

use App\User;
use App\Address;
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
			'first_name' => 'required|max:255',
			'last_name' => 'required|max:255',
			'email' => 'required|email|max:255|unique:users',
			'password' => 'required|confirmed|min:6',
			'business_name' => 'max:255',
			'billing_period' => 'required|in:monthly,annually',
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

		$billing = BillingDetail::create([
			'period' => $data['billing_period'],
			'address_id' => null,
			'dps_billing_token' => Session::get('billing.dps_billing_id'),
			'expiry_date' => Session::get('billing.date_expiry'),
			'last_billed' => null,
		]);

		if(strlen(trim($data['business_name']))) {
			$organisation = Organisation::create([
				'name' => $data['business_name'],
				'billing_detail_id' => $billing->id,
			]);
		}

		$user = User::create([
			'first_name' => $data['first_name'],
			'last_name' => $data['last_name'],
			'email' => $data['email'],
			'password' => bcrypt($data['password']),
			// User should belong to organisation of be billed directly, not both
			'organisation_id' => $organisation ? $organisation->id : null,
			'billing_detail_id' => $organisation ? null : $billing->id,
		]);

		// Add basic roles for the user
		$user->addRole('registered_user');
		// And org roles if registering as an organistaion - it's assumed the first user is an admin
		if($organisation) {
			$user->addRole('organisation_admin');
		}

		// Send out welcome email
		Mail::sendStyledMail('emails.welcome', ['name' => $user->fullName()], $user->email, $user->fullName(), 'Welcome to CataLex');

		return $user;
	}

}
