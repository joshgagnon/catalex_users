<?php namespace App\Http\Controllers;

use Input;
use Config;
use App\User;
use App\Address;
use App\Organisation;
use App\BillingDetail;
use App\Http\Requests\UserCreateRequest;
use App\Services\InviteBroker as PasswordBroker;

class AdminController extends Controller {

	/**
	 * The password broker implementation.
	 *
	 * @var PasswordBroker
	 */
	protected $passwordBroker;

	public function __construct(PasswordBroker $passwordBroker) {
		$this->passwordBroker = $passwordBroker;

		$this->middleware('admin');
	}

	public function getUsers() {
		$showDeleted = Input::has('deleted') && boolval(Input::get('deleted'));

		$userModel = User::withInactive()->orderBy('created_at');
		if($showDeleted) {
			$userModel = $userModel->withTrashed();
		}
		$userList = $userModel->paginate(Config::get('constants.items_per_page'));

		return view('admin.users', compact('showDeleted', 'userList'));
	}

	public function getOrganisations() {
		$showDeleted = Input::has('deleted') && boolval(Input::get('deleted'));

		// TODO: Populate org list

		return view('admin.organisations', compact('showDeleted'));
	}

	public function getAddUser() {
		$organisations = Organisation::all()->lists('name', 'id');
		$organisations = [0 => 'None'] + $organisations;

		return view('user.add', compact('organisations'));
	}

	public function postAddUser(UserCreateRequest $request) {
		$data = $request->all();

		$userData = [
			'first_name' => $data['first_name'],
			'last_name' => $data['last_name'],
			'email' => $data['email'],
			'password' => bcrypt(str_random(40)),
			'organisation_id' => null,
			'billing_detail_id' => null,
		];

		$orgId = null;
		if(boolval($data['organisation_id'])) {
			$orgId = intval($data['organisation_id']);

			if(!Organisation::find($orgId)) {
				return redirect()->back()->withErrors(['Unable to assign to organisation id ' . $data['organisation_id']]);
			}

			$userData['organisation_id'] = $orgId;
		}
		else {
			$address = Address::create([
				'line_1' => $data['address_line_1'],
				'line_2' => $data['address_line_2'],
				'city' => $data['city'],
				'state' => $data['state'],
				'iso3166_country' => $data['country'],
			]);

			$billing = BillingDetail::create([
				'period' => $data['billing_period'],
				'address_id' => $address->id,
			]);

			$userData['billing_detail_id'] = $billing->id;
		}

		$newUser = User::create($userData);

		if($orgId && $orgId == Config::get('constants.beta_organisation')) {
			$user->addRole('beta_tester');
		}
		else {
			$user->addRole('registered_user');
		}

		if($request->has('send_invite')) {
			$this->passwordBroker->sendResetLink(['email' => $newUser->email], function($mail) {
				$mail->subject('You have been invited to use CataLex Law Browser');
			});
		}

		return redirect()->action('AdminController@getUsers')->with('success', 'User ' . $newUser->fullName() . ' successfully created.');
	}
}
