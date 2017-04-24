<?php namespace App\Http\Requests;

use Auth;

use Illuminate\Foundation\Http\FormRequest;

class InviteFormRequest extends FormRequest {

	public function rules() {
		// TODO: Can Laravel validate as array-of-emails?
		return [
			'name' => 'required|max:255',
			'email' => 'required|email|max:255',
		];
	}

	public function authorize() {
		return Auth::check() && Auth::user()->can('edit_own_organisation');
	}
}
