<?php namespace App\Http\Controllers;

use App\BillingItem;
use App\Library\AdminStats;
use Auth;
use Input;
use Config;
use App\User;
use Validator;
use App\Address;
use App\AccessLog;
use App\Organisation;
use App\BillingDetail;
use Illuminate\Http\Request;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\CreateOrganisationRequest;
use App\Library\Invite;
use App\Library\BillingItemSummariser;


class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function allUsers() {
        $showDeleted = Input::has('deleted') && boolval(Input::get('deleted'));
        $userModel = User::withInactive()->orderBy('name');
        if($showDeleted) {
            $userModel = $userModel->withTrashed();
        }
        if(Input::has('filter') && strlen(Input::has('filter'))){
            $userModel = $userModel
            ->where(function ($query) {
                $filter = '%'.(Input::get('filter')).'%';
                $query
                ->where('name', 'like', $filter)
                ->orWhere('email', 'like', $filter);
            });
        }
        $userList = $userModel->paginate(Config::get('constants.items_per_page'));

        return view('admin.users', compact('showDeleted', 'userList'));
    }

    public function getOrganisations() {
        $showDeleted = Input::has('deleted') && boolval(Input::get('deleted'));

        $organisationModel = Organisation::orderBy('created_at');
        if($showDeleted) {
            $organisationModel = $organisationModel->withTrashed();
        }
        $organisations = $organisationModel->paginate(Config::get('constants.items_per_page'));

        return view('admin.organisations', compact('showDeleted', 'organisations'));
    }

    public function getAccessLog() {
        $filterEmail = trim(Input::get('filter_email', ''));
        if(!strlen($filterEmail)) {
            $filterEmail = null;
        }

        $includeUserLogins = Input::has('user_logins');
        $includeBrowserLogins = Input::has('browser_logins');
        $includeLogouts = Input::has('logouts');

        $logModel = AccessLog::whereNotNull('user_id')->orderBy('timestamp', 'DESC');

        if($includeUserLogins || $includeBrowserLogins || $includeLogouts) {
            $logModel->where(function($q) use($includeUserLogins, $includeBrowserLogins, $includeLogouts) {
                if($includeUserLogins) $q->orWhere('route', '=', 'auth/login');
                if($includeBrowserLogins) $q->orWhere('route', '=', 'browser-login');
                if($includeLogouts) $q->orWhere('route', '=', 'auth/logout');
            });
        }
        if($filterEmail) {
            $logModel->whereHas('user', function($q) use($filterEmail) {
                $q->where('email', 'like', '%' . $filterEmail . '%');
            });
        }

        $logs = $logModel->paginate(Config::get('constants.items_per_page'));

        return view('admin.access-log', compact('logs', 'filterEmail', 'includeUserLogins', 'includeBrowserLogins', 'includeLogouts'));
    }

    public function getAddUser() {
        $organisations = Organisation::get()->pluck('name', 'id')->toArray();
        $organisations = array_merge([0 => 'None'], $organisations);

        return view('user.add', compact('organisations'));
    }

    public function postAddUser(UserCreateRequest $request) {
        $data = $request->all();

        $userData = [
            'name' => $data['name'],
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
                'period' => !empty($data['billing_period']) ? $data['billing_period'] : 'monthly',
                'address_id' => $address->id,
            ]);

            $userData['billing_detail_id'] = $billing->id;
        }

        $newUser = User::create($userData);
        $newUser->addRole('registered_user');

        if($request->has('send_invite')) {
            Invite::sendInvite($newUser, Auth::user()->fullName());
        }

        return redirect()->route('admin.users')->with('success', 'User ' . $newUser->fullName() . ' successfully created.');
    }


    public function getCreateOrganisation() {
        return view('admin.create-organisation');
    }

    public function postCreateOrganisation(CreateOrganisationRequest $request) {
        $organisation = Organisation::create([
            'name' => $request->get('organisation_name'),
            'billing_detail_id' => null,
            'free' => true,
        ]);

        return redirect()->action('AdminController@getOrganisations')->with('success', 'Organisation "' . $organisation->name . '" successfully created.');
    }

    public function getEditOrganisation($id, $addMembers=null) {
        $organisation = Organisation::find($id);

        if(!$organisation) abort(404);

        if($addMembers === 'add-members') return $this->getAddMembers($organisation);

        return view('organisation.edit', compact('organisation'));
    }

    public function postEditOrganisation(Request $request, $id, $addMembers=null)
    {
        $organisation = Organisation::find($id);

        if (!$organisation) {
            abort(404);
        }

        if ($addMembers === 'add-members') {
            return $this->postAddMembers($request, $organisation);
        }

        $input = $request->all();
        $organisation->name = $input['name'];

        $submitter = Auth::user();
        $submitterIsGlobalAdmin = $submitter->hasRole('global_admin');

        if ($submitterIsGlobalAdmin) {
            $organisation->is_invoice_customer = boolval($request->input('is_invoice_customer', false));
        }

        $organisation->save();

        return redirect()->action('AdminController@postEditOrganisation', $organisation->id)->with('success', 'Organisation "' . $organisation->name . '" successfully updated.');
    }

    private function getAddMembers(Organisation $organisation) {
        return view('organisation.add-members', compact('organisation'));
    }

    private function postAddMembers(Request $request, Organisation $organisation) {
        $total = count($request->get('name'));

        $input = $request->all();

        $succeeded = [];
        $failed = [];

        for($i = 0; $i < $total; $i++) {
            $validator = Validator::make([
                'name' => $input['name'][$i],
                'email' => $input['email'][$i],
            ], [
                'name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users',
            ]);

            if($validator->fails()) {
                $failed[] = $input['name'][$i] . ' <' . $input['email'][$i] . '>';
                continue;
            }

            // TODO: Share code with OrganisationController@postInvite
            // Create a user for the invitee with random password
            $user = User::create([
                'name' => $input['name'][$i],
                'email' => $input['email'][$i],
                'password' => bcrypt(str_random(40)),
                'organisation_id' => $organisation->id,
                'billing_detail_id' => null,
            ]);
            $user->addRole('registered_user');

            Invite::sendInvite($user);

            $succeeded[] = $user;
        }

        $message = 'Successfully added ' . count($succeeded) . ' members.';
        if(count($failed)) {
            $message .= ' The following members could not be added: ' . implode(', ', $failed);
        }

        return redirect()->action('AdminController@getEditOrganisation', $organisation->id)->with('success', $message);
    }

    public function postDeleteOrganisation($id, $confirm=null) {
        $organisation = Organisation::find($id);

        if(!$organisation) abort(404);

        if($confirm !== 'confirm') {
            return view('organisation.delete', compact('organisation'));
        }

        foreach($organisation->members as $member) {
            $member->delete();
        }

        $orgName = $organisation->name;

        $organisation->delete();

        return redirect()->action('AdminController@getOrganisations')->with('success', 'Organisation "' . $orgName . '" successfully deleted.');
    }

    public function postUndeleteOrganisation($id) {
        $organisation = Organisation::onlyTrashed()->find($id);

        if(!$organisation) abort(404);

        $organisation->restore();

        foreach($organisation->membersWithTrashed as $member) {
            $member->restore();
        }

        return redirect()->back()->with('success', 'Organisation "' . $organisation->name . '" and ' . count($organisation->members) . ' members restored.');
    }

    public function billingOverview(BillingDetail $billingDetail)
    {
        $billable = null;
        $billableKeyName = null;

        if ($billingDetail->users()->first()) {
            $billable = $billingDetail->users()->first();
            $billableKeyName = 'subject';
        }
        else {
            $billable = $billingDetail->organisations()->first();
            $billableKeyName = 'organisation';
        }

        $chargeLogs = $billable->chargeLogs()->get();
        $billingItems = (new BillingItemSummariser($billable))->summarise();

        $discountPercent = null;

        if ($billable->billing_detail && $billable->billing_detail->discount_percent) {
            $discountPercent = $billable->billing_detail->discount_percent;
        }

        return view('billing.index')->with([
            'chargeLogs' => $chargeLogs,
            'billingItems' => $billingItems,
            $billableKeyName => $billable,
            'subscriptionUpToDate' => $billable->subscriptionUpToDate(),
            'discountPercent' => $discountPercent,
        ]);

        return view('billing.index');
    }

    public function stats()
    {
        $gcCompaniesStats = AdminStats::itemCount(BillingItem::ITEM_TYPE_GC_COMPANY);
        $totalGCCompanies = 0;

        $signSubscriptionCount = AdminStats::itemCount(BillingItem::ITEM_TYPE_SIGN_SUBSCRIPTION);
        $totalSignSubscriptions = 0;

        $gcCompaniesCounts = [];
        $signSubscriptionsCounts = [];

        foreach ($gcCompaniesStats as $statsRecord) {
            $gcCompaniesCounts[$statsRecord->condition] = $statsRecord->count;
            $totalGCCompanies += $statsRecord->count;
        }


        foreach ($signSubscriptionCount as $statsRecord) {
            $signSubscriptionsCounts[$statsRecord->condition] = $statsRecord->count;
            $totalSignSubscriptions += $statsRecord->count;
        }

        return view('admin.stats')->with([
            'totalGCCompanies' => $totalGCCompanies,
            'gcCompaniesCounts' => $gcCompaniesCounts,

            'totalSignSubscriptions' => $totalSignSubscriptions,
            'signSubscriptionsCounts' => $signSubscriptionsCounts,
        ]);
    }
}
