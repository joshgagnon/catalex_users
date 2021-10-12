<?php namespace App\Http\Controllers;

use App\AccessLog;
use App\Address;
use App\BillingDetail;
use App\BillingItem;
use App\Http\Requests\CreateOrganisationRequest;
use App\Http\Requests\UserCreateRequest;
use App\Library\AdminStats;
use App\Library\Billing;
use App\Library\BillingItemSummariser;
use App\Library\Invite;
use App\Organisation;
use App\User;
use Auth;
use Carbon\Carbon;
use Config;
use Illuminate\Http\Request;
use Input;
use Validator;


class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function allUsers()
    {
        $showDeleted = Input::has('deleted') && boolval(Input::get('deleted'));
        $userModel = User::withInactive()->orderBy('name');
        if ($showDeleted) {
            $userModel = $userModel->withTrashed();
        }
        if (Input::has('filter') && strlen(Input::has('filter'))) {
            $userModel = $userModel
                ->where(function ($query) {
                    $filter = '%' . (Input::get('filter')) . '%';
                    $query
                        ->where('name', 'like', $filter)
                        ->orWhere('email', 'like', $filter);
                });
        }
        $userList = $userModel->paginate(Config::get('constants.items_per_page'));

        return view('admin.users', compact('showDeleted', 'userList'));
    }

    public function getOrganisations()
    {
        $showDeleted = Input::has('deleted') && boolval(Input::get('deleted'));

        $organisationModel = Organisation::orderBy('created_at');
        if ($showDeleted) {
            $organisationModel = $organisationModel->withTrashed();
        }
        $organisations = $organisationModel->paginate(Config::get('constants.items_per_page'));

        return view('admin.organisations', compact('showDeleted', 'organisations'));
    }

    public function getAccessLog()
    {
        $filterEmail = trim(Input::get('filter_email', ''));
        if (!strlen($filterEmail)) {
            $filterEmail = null;
        }

        $includeUserLogins = Input::has('user_logins');
        $includeBrowserLogins = Input::has('browser_logins');
        $includeLogouts = Input::has('logouts');

        $logModel = AccessLog::whereNotNull('user_id')->orderBy('timestamp', 'DESC');

        if ($includeUserLogins || $includeBrowserLogins || $includeLogouts) {
            $logModel->where(function ($q) use ($includeUserLogins, $includeBrowserLogins, $includeLogouts) {
                if ($includeUserLogins) {
                    $q->orWhere('route', '=', 'auth/login');
                }
                if ($includeBrowserLogins) {
                    $q->orWhere('route', '=', 'browser-login');
                }
                if ($includeLogouts) {
                    $q->orWhere('route', '=', 'auth/logout');
                }
            });
        }
        if ($filterEmail) {
            $logModel->whereHas('user', function ($q) use ($filterEmail) {
                $q->where('email', 'like', '%' . $filterEmail . '%');
            });
        }

        $logs = $logModel->paginate(Config::get('constants.items_per_page'));

        return view('admin.access-log', compact('logs', 'filterEmail', 'includeUserLogins', 'includeBrowserLogins', 'includeLogouts'));
    }


    public function getCreateOrganisation()
    {
        return view('admin.create-organisation');
    }

    public function postCreateOrganisation(CreateOrganisationRequest $request)
    {
        $organisation = Organisation::create([
            'name'              => $request->get('organisation_name'),
            'billing_detail_id' => null,
            'free'              => true,
        ]);

        return redirect()->action('AdminController@getOrganisations')->with('success', 'Organisation "' . $organisation->name . '" successfully created.');
    }

    public function getEditOrganisation($id, $addMembers = null)
    {
        $organisation = Organisation::find($id);

        if (!$organisation) {
            abort(404);
        }

        if ($addMembers === 'add-members') {
            return $this->getAddMembers($organisation);
        }

        return view('organisation.edit', compact('organisation'));
    }

    public function postEditOrganisation(Request $request, $id, $addMembers = null)
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

        $organisation->is_invoice_customer = boolval($request->input('is_invoice_customer', false));
        $organisation->skip_billing = boolval($request->input('skip_billing', false));
        $organisation->force_no_access = boolval($request->input('force_no_access', false));

        if (!$organisation->skip_billing && $organisation->is_invoice_customer && !$organisation->billing_detail_id) {
            $billingDetails = BillingDetail::create([
                'period' => 'monthly',
                'billing_day' => Carbon::now()->addDays(Billing::DAYS_IN_TRIAL_PERIOD)->day,
            ]);

            $organisation->billing_detail_id = $billingDetails->id;
        }

        $organisation->save();

        return redirect()->action('AdminController@postEditOrganisation', $organisation->id)->with('success', 'Organisation "' . $organisation->name . '" successfully updated.');
    }

    private function getAddMembers(Organisation $organisation)
    {
        return view('organisation.add-members', compact('organisation'));
    }

    private function postAddMembers(Request $request, Organisation $organisation)
    {
        $total = count($request->get('name'));

        $input = $request->all();

        $succeeded = [];
        $failed = [];

        for ($i = 0; $i < $total; $i++) {
            $validator = Validator::make([
                'name'  => $input['name'][$i],
                'email' => $input['email'][$i],
            ], [
                'name'  => 'required|max:255',
                'email' => 'required|email|max:255|unique:users',
            ]);

            if ($validator->fails()) {
                $failed[] = $input['name'][$i] . ' <' . $input['email'][$i] . '>';
                continue;
            }

            // TODO: Share code with OrganisationController@postInvite
            // Create a user for the invitee with random password
            $user = User::create([
                'name'              => $input['name'][$i],
                'email'             => $input['email'][$i],
                'password'          => bcrypt(str_random(40)),
                'organisation_id'   => $organisation->id,
                'billing_detail_id' => null,
            ]);
            $user->addRole('registered_user');

            Invite::sendInvite($user);

            $succeeded[] = $user;
        }

        $message = 'Successfully added ' . count($succeeded) . ' members.';
        if (count($failed)) {
            $message .= ' The following members could not be added: ' . implode(', ', $failed);
        }

        return redirect()->action('AdminController@getEditOrganisation', $organisation->id)->with('success', $message);
    }

    public function postDeleteOrganisation($id, $confirm = null)
    {
        $organisation = Organisation::find($id);

        if (!$organisation) {
            abort(404);
        }

        if ($confirm !== 'confirm') {
            return view('organisation.delete', compact('organisation'));
        }

        foreach ($organisation->members as $member) {
            $member->delete();
        }

        $orgName = $organisation->name;

        $organisation->delete();

        return redirect()->action('AdminController@getOrganisations')->with('success', 'Organisation "' . $orgName . '" successfully deleted.');
    }

    public function postUndeleteOrganisation($id)
    {
        $organisation = Organisation::onlyTrashed()->find($id);

        if (!$organisation) {
            abort(404);
        }

        $organisation->restore();

        foreach ($organisation->membersWithTrashed as $member) {
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
            $skipBilling = false;
        }
        else {
            $billable = $billingDetail->organisations()->first();
            $billableKeyName = 'organisation';
            $skipBilling = $billable->skip_billing;
        }

        $chargeLogs = $billable->chargeLogs()->orderBy('timestamp', 'DESC')->get();
        $billingItems = (new BillingItemSummariser($billable))->summarise();

        $discountPercent = null;

        if ($billable->billing_detail && $billable->billing_detail->discount_percent) {
            $discountPercent = $billable->billing_detail->discount_percent;
        }

        return view('billing.index')->with([
            'chargeLogs'           => $chargeLogs,
            'billingItems'         => $billingItems,
            $billableKeyName       => $billable,
            'subscriptionUpToDate' => $billable->subscriptionUpToDate(),
            'discountPercent'      => $discountPercent,
            'skipBilling' => $skipBilling
        ]);

        return view('billing.index');
    }

    public function stats()
    {
        $gcCompaniesStats = AdminStats::itemCount(BillingItem::ITEM_TYPE_GC_COMPANY);
        $totalGCCompanies = 0;

        $signSubscriptionCount = AdminStats::itemCount(BillingItem::ITEM_TYPE_SIGN_SUBSCRIPTION);
        $totalSignSubscriptions = 0;

        $courtCostsSubscriptionCount = AdminStats::itemCount(BillingItem::ITEM_TYPE_COURT_COSTS_SUBSCRIPTION);
        $totalCourtCostsSubscriptions = 0;

        $gcCompaniesCounts = [];
        $signSubscriptionsCounts = [];
        $courtCostsSubscriptionsCounts = [];

        foreach ($gcCompaniesStats as $statsRecord) {
            $gcCompaniesCounts[$statsRecord->condition] = $statsRecord->count;
            $totalGCCompanies += $statsRecord->count;
        }

        foreach ($signSubscriptionCount as $statsRecord) {
            $signSubscriptionsCounts[$statsRecord->condition] = $statsRecord->count;
            $totalSignSubscriptions += $statsRecord->count;
        }

        foreach ($courtCostsSubscriptionCount as $statsRecord) {
            $courtCostsSubscriptionsCounts[$statsRecord->condition] = $statsRecord->count;
            $totalCourtCostsSubscriptions += $statsRecord->count;
        }

        return view('admin.stats')->with([
            'totalGCCompanies'  => $totalGCCompanies,
            'gcCompaniesCounts' => $gcCompaniesCounts,

            'totalSignSubscriptions'  => $totalSignSubscriptions,
            'signSubscriptionsCounts' => $signSubscriptionsCounts,

            'totalCourtCostsSubscriptions' => $totalCourtCostsSubscriptions,
            'courtCostsSubscriptionCount'  => $courtCostsSubscriptionCount,
        ]);
    }
}
