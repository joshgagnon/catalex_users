<?php namespace App\Http\Requests;

use Request;

class UserCreateRequest extends BaseRequest {

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		$rules = [
			'first_name' => 'required|max:255',
			'last_name' => 'required|max:255',
			'email' => 'required|email|max:255|unique:users',
			'address_line_1' => 'max:255',
			'address_line_2' => 'max:255',
			'city' => 'max:255',
			'state' => 'max:255',
			'organisation_id' => 'required',
			//'billing_period' => 'in:monthly,annually',
		];

		if(Request::get('organisation_id') == 0) {
			$rules['city'] = 'required|' . $rules['city'];
			$rules['country'] = 'required';
			//$rules['billing_period'] = 'required|' . $rules['billing_period'];
		}

		return $rules;
	}

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize() {
		// Controller middleware determines if this request is authorized
		return true;
	}
}
