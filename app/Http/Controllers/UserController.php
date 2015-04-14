<?php namespace App\Http\Controllers;

use Auth;
use App\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserEditRequest;

class UserController extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->middleware('auth');
	}

	/**
	 * Show current user profile details or edit form depending on permissions.
	 *
	 * @return Response
	 */
	public function getProfile() {
		$user = Auth::user();

		if($user->can('edit_own_user')) {
			return view('user.edit', ['subject' => $user]);
		}
		elseif ($user->can('view_own_user')) {
			return view('user.view', ['subject' => $user]);
		}

		return view('auth.denied');
	}

	/**
	 * Update a user's own details.
	 *
	 * @return Response
	 */
	public function postProfile(UserEditRequest $request) {
		$this->updateUser($request->input());

		return redirect()->action('UserController@getProfile');
	}

	/**
	 * Show a user's details by id.
	 *
	 * @return Response
	 */
	public function getView($subjectId) {
		$user = Auth::user();

		$subject = User::find($subjectId);

		if(!$subject) return view('auth.denied');

		if($user->can('view_any_user') ||
		   ($user->can('view_organisation_user') && $user->sharesOrganisation($subject)) ||
		   ($user->can('view_own_user') && $user->id === $subject->id)) {
			return view('user.view', compact('subject'));
		}

		return view('auth.denied');
	}

	/**
	 * Show the user edit form by id.
	 *
	 * @return Response
	 */
	public function getEdit($subjectId) {
		$user = Auth::user();

		$subject = User::find($subjectId);

		if(!$subject) return view('auth.denied');

		if($user->can('edit_any_user') ||
		   ($user->can('edit_organisation_user') && $user->sharesOrganisation($subject)) ||
		   ($user->can('edit_own_user') && $user->id === $subject->id)) {
			return view('user.edit', compact('subject'));
		}

		return view('auth.denied');
	}

	/**
	 * Update a user's details by id.
	 *
	 * @return Response
	 */
	public function postEdit(UserEditRequest $request, $subjectId) {
		$this->updateUser($request->input());

		return redirect()->action('UserController@getView', [$subjectId]);
	}

	/**
	 * Update a user from request input.
	 *
	 * @return void
	 */
	private function updateUser($input) {
		$user = User::find($input['user_id']);

		$user->first_name = $input['first_name'];
		$user->last_name = $input['last_name'];
		$user->email = $input['email'];

		if(strlen($input['new_password'])) {
			$user->password = bcrypt($input['new_password']);
		}

		$user->save();
	}

	/**
	 * Delete a user (soft delete only).
	 *
	 * @return Response
	 */
	public function postDelete($subjectId) {
		$user = Auth::user();

		$subject = User::withInactive()->find($subjectId);

		if(!$subject) return view('auth.denied');

		if(($user->can('edit_any_user') || ($user->can('edit_organisation_user') && $user->sharesOrganisation($subject))) &&
		   $user->id !== $subject->id) {
			$name = $subject->fullName();
			$subject->delete();
			return redirect()->back()->with('success', 'User "' . $name . '" successfully deleted.');
		}

		return view('auth.denied');
	}

	/**
	 * Restore a user from deleted status.
	 *
	 * @return Response
	 */
	public function postUndelete($subjectId) {
		$user = Auth::user();

		$subject = User::withInactive()->onlyTrashed()->find($subjectId);

		if(!$subject) return view('auth.denied');

		if(($user->can('edit_any_user') || ($user->can('edit_organisation_user') && $user->sharesOrganisation($subject))) &&
		   $user->id !== $subject->id) {
			$subject->restore();
			return redirect()->back()->with('success', 'User "' . $subject->fullName() . '" successfully restored.');
		}

		return view('auth.denied');
	}
}
