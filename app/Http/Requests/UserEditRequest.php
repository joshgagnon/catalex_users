<?php namespace App\Http\Requests;

use Auth;
use Input;
use Request;
use App\User;

class UserEditRequest extends BaseRequest {

	/**
	 * Provide validation rules for updating users.
	 *
	 * @return array
	 */
	public function rules() {
		$segments = array_values(Request::segments()); // Extra parens to force array copy
        $lastSegment = end($segments);
		$rules = [
			'name' => 'required|max:255',
			'email' => 'required|email|max:255|unique:users,email,',
			'new_password' => 'confirmed|min:6',
		];

		// If this route has an id at the end, user_id input must match it, otherwise must be yourself
		if(is_numeric($lastSegment)) {
			$userId = $lastSegment;
		}
		else {
			$userId = Auth::user()->id;
		}

		$rules['user_id'] = 'in:' . $userId;

		$rules['email'] .= $userId; // Avoid unique rule conflicting with current user

		return $rules;
	}

	/**
	 * Determine if the logged in user is permitted to edit the subject user.
	 *
	 * @return bool
	 */
	public function authorize() {
		$user = Auth::user();

		$subject = User::find(Input::get('user_id'));

		if(!$user || !$subject) return false;

		if($user->can('edit_any_user')) {
			return true;
		}

		if($user->can('edit_organisation_user') && $user->sharesOrganisation($subject)) {
			return true;
		}

		if($user->can('edit_own_user') && $user->id === $subject->id) {
			return true;
		}

		return false;
	}
}
