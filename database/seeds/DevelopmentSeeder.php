<?php

use App\BillingDetail;
use App\BillingItemPayment;
use App\ChargeLog;
use App\Library\Billing;
use App\Role;
use App\User;
use App\Organisation;
use App\BillingItem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call('RolePermissionSeeder');
        $this->call('ServiceSeeder');


        $admin = $this->createUser(['name' => 'admin', 'email' => 'admin@admin.com']);
        $admin->addRole(Role::where('name', '=', 'global_admin')->first());

        // Create some users
        $this->createUserWithBilling(['name' => 'Terry Carter', 'email' => 'terry@carter.com'], ['period' => 'monthly', 'billing_day' => 5]); // Yearly billing
        $this->createUserWithBilling(['name' => 'Sophie Watson', 'email' => 'sophie@watson.com'], ['period' => 'annually', 'billing_day' => 20]); // Monthly billing

        // Create an organisation with two users and billing
        $johnny = $this->createUser(['name' => 'Johnny Bouy', 'email' => 'johnny@bouy.com']);
        $org = $this->createOrganisationWithBilling($johnny, ['name' => 'Johnny Bouy\'s Adventure Group']);

        // Create two more users for the above org
        $this->createUser(['name' => 'Matt Johnson', 'email' => 'matt@johnson.com', 'organisation_id' => $org->id]);
        $jess = $this->createUser(['name' => 'Jess Walker', 'email' => 'jess@walker.com', 'organisation_id' => $org->id]);


        $gcService = App\Service::where('name', 'Good Companies')->first();

        $billingItems = [
            BillingItem::create(['item_type' => 'gc_company', 'service_id' => $gcService->id, 'item_id' => 1, 'user_id' => $johnny->id, 'json_data' => json_encode(['company_name' => 'Pacific Testing Limited'])]),
            BillingItem::create(['item_type' => 'gc_company', 'service_id' => $gcService->id, 'item_id' => 2, 'user_id' => $johnny->id, 'json_data' => json_encode(['company_name' => 'Kiwi Auto Trader'])]),
            BillingItem::create(['item_type' => 'gc_company', 'service_id' => $gcService->id, 'item_id' => 3, 'user_id' => $jess->id, 'json_data' => json_encode(['company_name' => 'Mary\'s Accountaing Service'])]),
        ];

        $billingDay = Carbon::today()->day($org->billing_detail->billing_day);
        $this->billOrgForDate($org, $billingItems, $billingDay->copy()->subMonths(3));
        $this->billOrgForDate($org, $billingItems, $billingDay->copy()->subMonths(2));
        $this->billOrgForDate($org, $billingItems, $billingDay->copy()->subMonths(1));
    }

    private function billOrgForDate($org, $billingItems, $date)
    {
        $centsPerItem = 150;
        $totalAmount = Billing::centsToDollars(sizeof($billingItems) * $centsPerItem);
        $chargeLog = ChargeLog::create(['organisation_id' => $org->id, 'success' => true, 'total_amount' => $totalAmount, 'gst' => Billing::includingGst($totalAmount), 'timestamp' => $date, 'pending' => false]);


        $paidUntil = $date->addMonth(1);
        foreach ($billingItems as $item) {
            $this->createBillingItemPayment($paidUntil, $item->id, $chargeLog->id, Billing::centsToDollars($centsPerItem));
        }
    }

    private function createBillingItemPayment($paidUntil, $billingItemId, $chargeLogId, $amount)
    {
        return BillingItemPayment::create([
            'paid_until' => $paidUntil,
            'billing_item_id' => $billingItemId,
            'charge_log_id' => $chargeLogId,
            'amount' => $amount,
            'gst' => Billing::includingGst($amount)
        ]);
    }

    protected function createUser($overrides=[])
    {
        $defaults = [
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => Hash::make('qssycu44'),
            'active' => true,
            'billing_detail_id' => null,
        ];

        $userData = array_merge($defaults, $overrides);

        $user = User::create($userData);

        // Add the registered user role
        $registeredRole = Role::where('name', 'registered_user')->first();
        $user->addRole($registeredRole);

        return $user;
    }

    protected function createBillingDetails($overrides=[])
    {
        $defaults = [
            'period' => 'monthly',
            'billing_day' => 1,
        ];

        $billingData = array_merge($defaults, $overrides);

        return BillingDetail::create($billingData);
    }

    protected function createUserWithBilling($userOverrides=[], $billingOverrides=[])
    {
        $billingDetail = $this->createBillingDetails($billingOverrides);

        $userData = array_merge(['billing_detail_id' => $billingDetail->id], $userOverrides);

        $user = $this->createUser($userData);

        return $user;
    }

    protected function createOrganisation($orgAdmin, $overrides=[])
    {
        $defaults = ['name' => 'Test Org'];
        $orgData = array_merge($defaults, $overrides);

        $organisation = Organisation::create($orgData);

        $orgAdmin->organisation_id = $organisation->id;
        $orgAdmin->save();

        $orgAdmin->addRole('organisation_admin');

        return $organisation;
    }

    private function createOrganisationWithBilling($orgAdmin, $orgOverrides=[], $billingOverrides=[])
    {
        $billingDetail = $this->createBillingDetails($billingOverrides);

        $userData = array_merge(['billing_detail_id' => $billingDetail->id], $orgOverrides);

        $org = $this->createOrganisation($orgAdmin, $userData);

        return $org;
    }
}
