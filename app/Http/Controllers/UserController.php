<?php namespace App\Http\Controllers;

use App\FirstLoginToken;
use App\Http\Requests\UserEditRequest;
use App\Library\Invite;
use App\Library\Mail\InviteNewUserToSignDocument;
use App\Library\Mail\InviteNewUserToViewGCCompany;
use App\Library\Mail\InviteToSignDocument;
use App\Library\Mail\InviteToViewGCCompany;
use App\Library\UserSummariser;
use App\User;
use Auth;
use DB;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use LucaDegasperi\OAuth2Server\Authorizer;


/**
 * Class UserController
 *
 * @package App\Http\Controllers
 */
class UserController extends Controller
{
	/**
	 * Show current user profile details or edit form depending on permissions.
	 *
	 * @return View
	 */
	public function getProfile()
    {
		$user = Auth::user();

		if ($user->can('edit_own_user')) {
			$editServicesAndBilling = true;

			if ($user && $user->organisation && !$user->can('edit_own_organisation')) {
				$editServicesAndBilling = false;
	        }

			return view('user.edit')->with([
				'subject' => $user,
				'editServicesAndBilling' => $editServicesAndBilling,
			]);
		} elseif ($user->can('view_own_user')) {
			return view('user.view', ['subject' => $user]);
		}

		return view('auth.denied');
	}

    /**
     * Update a user's own details.
     *
     * @param UserEditRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */public function postProfile(UserEditRequest $request)
    {
		$this->updateUser($request->input());

		return redirect()->action('UserController@getProfile');
	}

    /**
     * Show a user's details by id.
     *
     * @param $subjectId
     *
     * @return View
     */public function getView($subjectId)
    {
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
	 * @return View
	 */
	public function getEdit($subjectId)
    {
		$user = Auth::user();
		$subject = User::find($subjectId);

		if (!$subject) {
			return view('auth.denied');
		}

		if ($user->can('edit_any_user') ||
		   ($user->can('edit_organisation_user') && $user->sharesOrganisation($subject)) ||
		   ($user->can('edit_own_user') && $user->id === $subject->id)) {
			$roles = [];
			$free = null;

			if ($user->hasRole('global_admin')) {
				$roles = ['global_admin' => $subject->hasRole('global_admin'), 'organisation_admin' => $subject->hasRole('organisation_admin')];
			} elseif ($user->can('edit_organisation_user') && $user->sharesOrganisation($subject)) {
				$roles = ['organisation_admin' => $subject->hasRole('organisation_admin')];
			}

            $editServicesAndBilling = false;
			return view('user.edit', compact('subject', 'roles', 'editServicesAndBilling'));
		}

		return view('auth.denied');
	}

	/**
	 * Update a user's details by id.
	 *
	 * @return Response
	 */
	public function postEdit(UserEditRequest $request, $subjectId)
    {
		$this->updateUser($request->input());

		return redirect()->action('UserController@getEdit', [$subjectId]);
	}

	/**
	 * Update a user from request input.
	 *
	 * @return void
	 */
	private function updateUser($input)
    {
		$user = User::find($input['user_id']);

		$user->name = $input['name'];
		$user->email = $input['email'];

		if (Auth::user()->hasRole('global_admin')) {
			$input['free'] = empty($input['free']) ? false : $input['free'];
			$user->free = $input['free'] == true;
		}

		if(strlen($input['new_password'])) {
			$user->password = bcrypt($input['new_password']);
		}

		// Update submitted roles, verifying the authed user is allowed to change them
		$submitter = Auth::user();
		if(isset($input['global_admin']) &&
		   $submitter->hasRole('global_admin')) {
			if(boolval($input['global_admin'])) {
				$user->addRole('global_admin');
			}
			else {
				$user->removeRole('global_admin');
			}
		}
		if(isset($input['organisation_admin']) &&
		   ($submitter->hasRole('global_admin') || ($submitter->can('edit_organisation_user') && $submitter->sharesOrganisation($user)))) {
			if(boolval($input['organisation_admin'])) {
				$user->addRole('organisation_admin');
			}
			else {
				$user->removeRole('organisation_admin');
			}
		}

		$user->save();
	}

	/**
	 * Delete a user (soft delete only).
	 *
	 * @return Response
	 */
	public function postDelete($subjectId)
    {
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
	public function postUndelete($subjectId)
    {
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

    /**
     * @param Authorizer $authorizer
     *
     * @return array
     */public function info(Authorizer $authorizer)
    {
        $user_id = $authorizer->getResourceOwnerId(); // the token user_id
        $user = User::find($user_id); // get the user data from database

        $userSummary = (new UserSummariser($user))->summarise();

        return $userSummary;
    }

    public function createOrFindUser(Request $request) {
        $client = DB::table('oauth_clients')
            ->where('id', $request->input('client_id'))
            ->where('secret', $request->input('client_secret'))
            ->first();

        if (!$client) {
            return view('auth.denied');
        }

        $user = User::where('email', $request->input('email'))->first();
        $isExistingUser = $user !== null;

        if (!$isExistingUser) {
            $userData = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => bcrypt(str_random(40)),
                'organisation_id' => null,
                'billing_detail_id' => null,
            ];

            $user = User::create($userData);
            $user->addRole('registered_user');
        }

        $serviceType = $request->has('service') ? $request->input('service') : 'Good Companies';
        $inviterName = $request->input('sender_name');

        switch ($serviceType) {
            case 'Good Companies':
                if ($isExistingUser) {
                    $invite = new InviteToViewGCCompany($user, $request->input('company_name'), $request->input('sender_name'));
                    $invite->send();
                }
                else {
                    $tokenInstance = FirstLoginToken::createToken($user);
                    $companyName = $request->input('company_name');

                    $invite = new InviteNewUserToViewGCCompany($user, $inviterName, $companyName, $tokenInstance->token);
                    $invite->send();
                }

                break;

            case 'CataLex Sign':
                if ($isExistingUser) {
                    $invite = new InviteToSignDocument($user, $inviterName);
                    $invite->send();
                }
                else {
                    $tokenInstance = FirstLoginToken::createToken($user);

                    $invite = new InviteNewUserToSignDocument($user, $inviterName, $tokenInstance->token);
                    $invite->send();
                }

                break;
        }

        $userSummary = (new UserSummariser($user))->summarise();

        return $userSummary;
    }
}
