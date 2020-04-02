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
			'name' => 'required|max:255',
			'email' => 'required|email|max:255|unique:users'
			//'billing_period' => 'in:monthly,annually',
		];

        if(Request::user()->hasRole('global_admin')) {

            $rules['organisation_id'] = 'required';
            $rules['address_line_1'] = 'max:255';
            $rules['address_line_2'] = 'max:255';
            $rules['city'] = 'max:255';
            $rules['state'] = 'max:255';

    		if(Request::get('organisation_id') == 0) {
    			$rules['city'] = 'required|' . $rules['city'];
    			$rules['country'] = 'required';
    			//$rules['billing_period'] = 'required|' . $rules['billing_period'];
    		}
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
