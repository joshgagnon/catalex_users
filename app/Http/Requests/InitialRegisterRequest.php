<?php namespace App\Http\Requests;

use Auth;

class InitialRegisterRequest extends BaseRequest {

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		return [
			'first_name' => 'required|max:255',
			'last_name' => 'required|max:255',
			'email' => 'required|email|max:255|unique:users',
			'password' => 'required|confirmed|min:6',
			'business_name' => 'max:255',
			'customer_agreement' => 'accepted',
		];
	}

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize() {
		// Only users not yet logged in can start registration
		return Auth::guest();
	}
}
