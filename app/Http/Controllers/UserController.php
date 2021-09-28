<?php namespace App\Http\Controllers;

use App\FirstLoginToken;
use App\Http\Requests\UserEditRequest;
use App\Library\Mail\InviteNewUserToSignDocument;
use App\Library\Mail\InviteNewUserToViewGCCompany;
use App\Library\Mail\InviteToSignDocument;
use App\Library\Mail\InviteToViewGCCompany;
use App\Library\UserSummariser;
use App\Address;
use App\BillingDetail;
use App\BillingItem;
use App\Http\Requests\UserCreateRequest;
use App\Library\Billing;
use App\Library\BillingItemSummariser;
use App\Library\Invite;
use App\Organisation;

use App\User;
use Auth;
use Config;
use DB;
use Hash;
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
            return view('user.edit')->with(['subject' => $user]);
        }
        elseif ($user->can('view_own_user')) {
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
     */
    public function postProfile(UserEditRequest $request)
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
     */
    public function getView($subjectId)
    {
        $user = Auth::user();

        $subject = User::find($subjectId);

        if (!$subject) {
            return view('auth.denied');
        }

        if ($user->can('view_any_user') ||
            ($user->can('view_organisation_user') && $user->sharesOrganisation($subject)) ||
            ($user->can('view_own_user') && $user->id === $subject->id)
        ) {
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
            ($user->can('edit_own_user') && $user->id === $subject->id)
        ) {
            $roles = [];
            $free = null;

            if ($user->hasRole('global_admin')) {
                $roles = ['global_admin' => $subject->hasRole('global_admin'), 'organisation_admin' => $subject->hasRole('organisation_admin')];
            }
            elseif ($user->can('edit_organisation_user') && $user->sharesOrganisation($subject)) {
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

        if (strlen($input['new_password'])) {
            $user->password = bcrypt($input['new_password']);
        }

        // Update submitted roles, verifying the authed user is allowed to change them
        $submitter = Auth::user();
        $submitterIsGlobalAdmin = $submitter->hasRole('global_admin');

        if ($submitterIsGlobalAdmin) {
            if (isset($input['global_admin'])) {
                if (boolval($input['global_admin'])) {
                    $user->addRole('global_admin');
                }
                else {
                    $user->removeRole('global_admin');
                }
            }

            if (!$user->organisation_id) {
                $user->is_invoice_customer = empty($input['is_invoice_customer']) ? false : boolval($input['is_invoice_customer']);
            }
        }

        $submitterCanSetOrgAdmin = $submitter->can('edit_organisation_user') && $submitter->sharesOrganisation($user);

        if (isset($input['organisation_admin']) && ($submitterIsGlobalAdmin || $submitterCanSetOrgAdmin)) {
            if (boolval($input['organisation_admin'])) {
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

        if (!$subject) {
            return view('auth.denied');
        }

        if (($user->can('edit_any_user') || ($user->can('edit_organisation_user') && $user->sharesOrganisation($subject))) && $user->id !== $subject->id) {
            $name = $subject->fullName();
            $subject->delete();
            return redirect()->back()->with('success', 'User "' . $name . '" successfully deleted.');
        }

        return view('auth.denied');
    }

    /**
     * Restore a user from deleted status.
     *
     * @param $subjectId
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function postUndelete($subjectId)
    {
        $user = Auth::user();

        $subject = User::withInactive()->onlyTrashed()->find($subjectId);

        if (!$subject) {
            return view('auth.denied');
        }

        if (($user->can('edit_any_user') || ($user->can('edit_organisation_user') && $user->sharesOrganisation($subject))) && $user->id !== $subject->id) {
            $subject->restore();
            return redirect()->back()->with('success', 'User "' . $subject->fullName() . '" successfully restored.');
        }

        return view('auth.denied');
    }

    /**
     * @param Authorizer $authorizer
     *
     * @return array
     */
    public function info(Authorizer $authorizer)
    {
        $user_id = $authorizer->getResourceOwnerId(); // the token user_id
        $user = User::find($user_id); // get the user data from database

        $userSummary = (new UserSummariser($user))->summarise();

        return $userSummary;
    }

    public function apiUserInfo(Request $request, User $user)
    {
        $client = DB::table('oauth_clients')
            ->where('id', $request->input('client_id'))
            ->where('secret', $request->input('client_secret'))
            ->first();

        if (!$client) {
            return view('auth.denied');
        }

        $userSummary = (new UserSummariser($user))->summarise();
        return $userSummary;
    }

    public function createOrFindUser(Request $request)
    {
        $client = DB::table('oauth_clients')
            ->where('id', $request->input('client_id'))
            ->where('secret', $request->input('client_secret'))
            ->first();

        if (!$client) {
            return view('auth.denied');
        }

        $user = User::where('email', 'ilike', $request->input('email'))->first();
        $isExistingUser = $user !== null;

        if (!$isExistingUser) {
            $userData = [
                'name'              => $request->input('name'),
                'email'             => $request->input('email'),
                'password'          => bcrypt(str_random(40)),
                'organisation_id'   => null,
                'billing_detail_id' => null,
            ];

            $user = User::create($userData);
            $user->addRole('registered_user');
        }

        $inviterName = $request->input('sender_name');

        switch ($client->id) {
            case Config::get('oauth_clients.gc.id'):
                $companyName = $request->input('company_name');

                if ($isExistingUser) {
                    $invite = new InviteToViewGCCompany($user, $inviterName, $companyName);
                    $invite->send();
                }
                else {
                    $tokenInstance = FirstLoginToken::createToken($user);

                    $invite = new InviteNewUserToViewGCCompany($user, $inviterName, $companyName, $tokenInstance->token);
                    $invite->send();
                }

                break;

            case Config::get('oauth_clients.sign.id'):
                $link = $request->input('link');

                if ($isExistingUser) {
                    $invite = new InviteToSignDocument($user, $inviterName, $link);
                    $invite->send();
                }
                else {
                    $tokenInstance = FirstLoginToken::createToken($user);

                    $invite = new InviteNewUserToSignDocument($user, $inviterName, $link, $tokenInstance->token);
                    $invite->send();
                }

                break;
        }

        $userSummary = (new UserSummariser($user))->summarise();

        return $userSummary;
    }

    public function findOrCreateUsers(Request $request)
    {
        $requestData = $request->all();

        $client = DB::table('oauth_clients')
            ->where('id', $requestData['client_id'])
            ->where('secret', $requestData['client_secret'])
            ->first();

        if (!$client) {
            return view('auth.denied');
        }

        $users = json_decode($requestData['users'], true);
        $userSummaries = [];

        $inviterName = $requestData['sender_name'];

        foreach ($users as $userData) {
            $user = User::where('email', 'ilike', $userData['email'])->withTrashed()->first();
            $isExistingUser = $user !== null;

            if (!$isExistingUser) {
                $user = User::create([
                    'name'              => $userData['name'],
                    'email'             => $userData['email'],
                    'password'          => Hash::make(str_random(40)),
                    'organisation_id'   => null,
                    'billing_detail_id' => null,
                ]);

                $user->addRole('registered_user');
                $user->meta()->create([
                    'data' => [
                        'invite' => [
                            'oauth_client_id' => $client->id,
                            'inviter_name'    => $inviterName,
                        ],
                    ],
                ]);
            }
            else {
                if($user->trashed()) {
                    $user->restore();
                }
            }
            switch ($client->id) {
                case Config::get('oauth_clients.gc.id'):
                    $companyName = $userData['company_name'];

                    if ($isExistingUser) {
                        $invite = new InviteToViewGCCompany($user, $inviterName, $companyName);
                        $invite->send();
                    }
                    else {
                        $tokenInstance = FirstLoginToken::createToken($user);

                        $invite = new InviteNewUserToViewGCCompany($user, $inviterName, $companyName, $tokenInstance->token);
                        $invite->send();
                    }

                    break;

                case Config::get('oauth_clients.sign.id'):
                    $link = $userData['link'];
                    $message = empty($userData['message']) ? null : $userData['message'];

                    if ($isExistingUser && !$user->is_shadow_user) {
                        $invite = new InviteToSignDocument($user, $inviterName, $link, $message);
                        $invite->send();
                    }
                    else {
                        $user->update(['is_shadow_user' => true]);

                        $tokenInstance = FirstLoginToken::createToken($user);

                        $invite = new InviteNewUserToSignDocument($user, $inviterName, $link, $tokenInstance->token, $message);
                        $invite->send();
                    }

                    break;
            }
            $userSummary = (new UserSummariser($user))->summarise();
            $userSummary['requestedEmail'] = $userData['email'];
            $userSummaries[] = $userSummary;
        }

        return $userSummaries;
    }

    public function userRedirectLogin(Request $request)
    {
        $requestData = $request->all();

        $client = DB::table('oauth_clients')
            ->where('id', $requestData['client_id'])
            ->where('secret', $requestData['client_secret'])
            ->first();

        if (!$client) {
            return view('auth.denied');
        }

        $user = User::where('email', 'ilike', $requestData['email'])->first();
        if ($user->is_shadow_user) {
            return response()->json(['url' => route('request-login-token', ['user_id' => $user->id, 'next' => $request->next])]);
        }
        else {
            return response()->json(['url' => url() . '/auth/login?next=' . $request->next]);
        }
    }


    public function getAddUser()
    {
        $organisations = Organisation::get()->pluck('name', 'id')->toArray();
        $organisations = array_merge([0 => 'None'], $organisations);

        return view('user.add', compact('organisations'));
    }

    public function postAddUser(UserCreateRequest $request)
    {
        $data = $request->all();

        $userData = [
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => bcrypt(str_random(40)),
            'organisation_id'   => null,
            'billing_detail_id' => null,
        ];

        $orgId = null;
        if($request->user()->hasRole('global_admin')) {
            if (boolval($data['organisation_id'])) {
                $orgId = intval($data['organisation_id']);

                if (!Organisation::find($orgId)) {
                    return redirect()->back()->withErrors(['Unable to assign to organisation id ' . $data['organisation_id']]);
                }

                $userData['organisation_id'] = $orgId;
            }
            else {
                $address = Address::create([
                    'line_1'          => $data['address_line_1'],
                    'line_2'          => $data['address_line_2'],
                    'city'            => $data['city'],
                    'state'           => $data['state'],
                    'iso3166_country' => $data['country'],
                ]);

                $billing = BillingDetail::create([
                    'period'     => !empty($data['billing_period']) ? $data['billing_period'] : 'monthly',
                    'address_id' => $address->id,
                ]);

                $userData['billing_detail_id'] = $billing->id;
            }
        }
        $newUser = User::create($userData);
        $newUser->addRole('registered_user');

        if ($request->has('send_invite')) {
            Invite::sendInvite($newUser, Auth::user()->fullName());
        }
        if($request->user()->hasRole('global_admin')) {
            return redirect()->route('admin.users')->with('success', 'User ' . $newUser->fullName() . ' successfully created.');
        }
         return redirect()->route('index')->with('success', 'User ' . $newUser->fullName() . ' successfully created.');
    }


}

