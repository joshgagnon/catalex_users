<?php namespace App\Http\Requests;

use Auth;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrganisationRequest extends FormRequest {

	public function rules() {
		return [
			'organisation_name' => 'required|max:255|unique:organisations,name',
		];
	}

	public function authorize() {
		return Auth::check() && !Auth::user()->organisation;
	}
}
