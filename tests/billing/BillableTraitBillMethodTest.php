<?php

use App\BillingItem;
use App\Service;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Stub\User;

class Organisation extends \App\Organisation
{
    public $amountRequested;

    protected function requestPayment($amountRequested)
    {
        $this->amountRequested = $amountRequested;
        return true; // Don't do any payment stuff, just pretend it worked
    }
}

class BillableTraitBillMethodTest extends TestCase
{
    use DatabaseTransactions;

    private function createService($name, $paid = null)
    {
        return Service::create([
            'name'            => $name,
            'is_paid_service' => $paid ?: true,
        ]);
    }

    private function massCreateUser($numberOfUsers, $organisationId = null, $serviceIds = [])
    {
        $users = [];

        for ($index = 0; $index < $numberOfUsers; $index++) {
            $users[] = $this->createUser(['organisation_id' => $organisationId], $serviceIds);
        }

        return $users;
    }

    /**
     * @test
     */
    public function bill_user_without_billing_details()
    {
        // Create a user
        $user = $this->createUser();

        // Create a service and attach it to the user
        $service = $this->createService('Good Companies');
        $user->services()->attach($service);

        // Create a billing item
        BillingItem::create(['user_id' => $user->id, 'service_id' => $service->id, 'item_id' => 1, 'item_type' => 'gc_company', 'json_data' => '{\"company_name\":\"test\"}', 'active' => true]);

        // Bill the user
        $billingResult = $user->bill();
        $this->assertFalse($billingResult);

        // Check the user wasn't billed
        $this->assertNull($user->amountRequested);
    }

    /**
     * @test
     */
    public function bill_user_annually()
    {
        // Create a user
        $billingDetails = $this->createBillingDetails(['period' => 'annually']);
        $user = User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => bcrypt('password'), 'active' => true, 'billing_detail_id' => $billingDetails->id]);

        // Create a service and attach it to the user
        $service = $this->createService('Good Companies');
        $user->services()->attach($service);

        // Create a billing item
        BillingItem::create(['user_id' => $user->id, 'service_id' => $service->id, 'item_id' => 1, 'item_type' => 'gc_company', 'json_data' => '{\"company_name\":\"test\"}', 'active' => true]);

        // Bill the user
        $user->bill();

        // Check the result
        $actual = $user->amountRequested;
        $expected = '12.00';

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function bill_user_monthly()
    {
        // Create a user
        $billingDetails = $this->createBillingDetails(['period' => 'monthly']);
        $user = User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => bcrypt('password'), 'active' => true, 'billing_detail_id' => $billingDetails->id]);

        // Create a service and attach it to the user
        $service = $this->createService('Good Companies');
        $user->services()->attach($service);

        // Create a billing item
        BillingItem::create(['user_id' => $user->id, 'service_id' => $service->id, 'item_id' => 1, 'item_type' => 'gc_company', 'json_data' => '{\"company_name\":\"test\"}', 'active' => true]);

        // Bill the user
        $user->bill();

        // Check the result
        $actual = $user->amountRequested;
        $expected = '1.50';

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function bill_user_annually_noBillingItems()
    {
        // Create a user
        $billingDetails = $this->createBillingDetails(['period' => 'annually']);
        $user = User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => bcrypt('password'), 'active' => true, 'billing_detail_id' => $billingDetails->id]);

        // Create a service and attach it to the user
        $service = $this->createService('Good Companies');
        $user->services()->attach($service);

        // Bill the user
        $user->bill();

        // Check the result
        $actual = $user->amountRequested;
        $expected = '0.00';

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function bill_user_monthly_noBillingItems()
    {
        // Create a user
        $billingDetails = $this->createBillingDetails(['period' => 'monthly']);
        $user = User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => bcrypt('password'), 'active' => true, 'billing_detail_id' => $billingDetails->id]);

        // Create a service and attach it to the user
        $service = $this->createService('Good Companies');
        $user->services()->attach($service);

        // Bill the user
        $user->bill();

        // Check the result
        $actual = $user->amountRequested;
        $expected = '0.00';

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function bill_user_annually_twoBillingItems()
    {
        // Create a user
        $billingDetails = $this->createBillingDetails(['period' => 'annually']);
        $user = User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => bcrypt('password'), 'active' => true, 'billing_detail_id' => $billingDetails->id]);

        // Create a service and attach it to the user
        $service = $this->createService('Good Companies');
        $user->services()->attach($service);

        // Create the billing items
        $this->massCreateBillingItems($user->id, $service->id, 2);

        // Bill the user
        $user->bill();

        // Check the result
        $actual = $user->amountRequested;
        $expected = '24.00';

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function bill_user_monthly_twoBillingItems()
    {
        // Create a user
        $billingDetails = $this->createBillingDetails(['period' => 'monthly']);
        $user = User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => bcrypt('password'), 'active' => true, 'billing_detail_id' => $billingDetails->id]);

        // Create a service and attach it to the user
        $service = $this->createService('Good Companies');
        $user->services()->attach($service);

        // Create the billing items
        $this->massCreateBillingItems($user->id, $service->id, 2);

        // Bill the user
        $user->bill();

        // Check the result
        $actual = $user->amountRequested;
        $expected = '3.00';

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function bill_user_annually_lotsOfBillingItems()
    {
        // Create a user
        $billingDetails = $this->createBillingDetails(['period' => 'annually']);
        $user = User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => bcrypt('password'), 'active' => true, 'billing_detail_id' => $billingDetails->id]);

        // Create a service and attach it to the user
        $service = $this->createService('Good Companies');
        $user->services()->attach($service);

        // Create a billing items
        $numberOfBillingItems = 277;
        $this->massCreateBillingItems($user->id, $service->id, $numberOfBillingItems);

        // Bill the user
        $user->bill();

        // Check the result
        $actual = $user->amountRequested;
        $expected = '3324.00';

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function bill_user_monthly_lotsOfBillingItems()
    {
        // Create a user
        $billingDetails = $this->createBillingDetails(['period' => 'monthly']);
        $user = User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => bcrypt('password'), 'active' => true, 'billing_detail_id' => $billingDetails->id]);

        // Create a service and attach it to the user
        $service = $this->createService('Good Companies');
        $user->services()->attach($service);

        // Create a billing items
        $numberOfBillingItems = 277;
        $this->massCreateBillingItems($user->id, $service->id, $numberOfBillingItems);

        // Bill the user
        $user->bill();

        // Check the result
        $expected = '415.50';
        $actual = $user->amountRequested;

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function bill_organisation_oneUser_oneItem()
    {
        // Create the organisation
        $billingDetails = $this->createBillingDetails(['period' => 'annually']);
        $organisation = Organisation::create(['name' => 'Org 1', 'billing_detail_id' => $billingDetails->id]);

        // Create a user that is subscribed to GC
        $gcService = Service::where('name', 'Good Companies')->first();
        $user1 = $this->createUser(['organisation_id' => $organisation->id], [$gcService->id]);

        // Create a billing item
        BillingItem::create([
            'user_id'    => $user1->id,
            'service_id' => $gcService->id,
            'item_id'    => 1,
            'item_type'  => 'gc_company',
            'json_data'  => '{\"company_name\":\"test\"}',
            'active'     => true,
        ]);

        // Bill the organisation
        $organisation->bill();

        // Check the result
        $actual = $organisation->amountRequested;
        $expected = '12.00';

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function bill_organisation_oneUser_lotsOfItems()
    {
        // Create the organisation
        $billingDetails = $this->createBillingDetails(['period' => 'monthly']);
        $organisation = Organisation::create(['name' => 'Org 1', 'billing_detail_id' => $billingDetails->id]);

        // Create a user that is subscribed to GC
        $gcService = Service::where('name', 'Good Companies')->first();
        $user1 = $this->createUser(['organisation_id' => $organisation->id], [$gcService->id]);

        $numberOfBillingItems = 127;
        $this->massCreateBillingItems($user1->id, $gcService->id, $numberOfBillingItems);

        // Bill the organisation
        $organisation->bill();

        // Check the result
        $actual = $organisation->amountRequested;
        $expected = '190.50';

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function bill_organisation_multipleUsers_oneItemPerUser()
    {
        // Create the organisation
        $billingDetails = $this->createBillingDetails(['period' => 'annually']);
        $organisation = Organisation::create(['name' => 'Org 1', 'billing_detail_id' => $billingDetails->id]);

        // Create a few users
        $gcService = Service::where('name', 'Good Companies')->first();
        $users = $this->massCreateUser(4, $organisation->id, [$gcService->id]);

        // Give each user some billing items
        $billingItems = [];
        foreach ($users as $index => $user) {
            $billingItems[] = [
                'user_id'    => $user->id,
                'service_id' => $gcService->id,
                'item_id'    => $index,
                'item_type'  => 'gc_company',
                'json_data'  => '{\"company_name\":\"test\"}',
                'active'     => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        BillingItem::insert($billingItems);

        // Bill the organisation
        $organisation->bill();

        // Check the result
        $actual = $organisation->amountRequested;
        $expected = '48.00'; // 4 users * $12 a year

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function bill_organisation_multipleUsers_multipleItemsPerUser()
    {
        // Create the organisation
        $billingDetails = $this->createBillingDetails(['period' => 'annually']);
        $organisation = Organisation::create(['name' => 'Org 1', 'billing_detail_id' => $billingDetails->id]);

        // Create a few users
        $numberOfUsers = 4;
        $numberOfItemsPerUser = 10;
        $gcService = Service::where('name', 'Good Companies')->first();
        $users = $this->massCreateUser($numberOfUsers, $organisation->id, [$gcService->id]);

        // Give each user the
        foreach ($users as $index => $user) {
            $this->massCreateBillingItems($user->id, $gcService->id, $numberOfItemsPerUser);
        }

        // Bill the organisation
        $organisation->bill();

        // Check the result
        $actual = $organisation->amountRequested;
        $expected = '480.00'; // 4 users * 10 items per user * $12 a year

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     *
     * Test the bill method charges the monthly price for a subscription
     */
    public function bill_charges_for_monthly_subscriptions()
    {
        $sign = Service::where('name', 'CataLex Sign')->first();
        $user = $this->createUserWithBilling([], [], [$sign->id]);

        $user->bill();

        $this->assertEquals($user->amountRequested, '6.00');
    }

    /**
     * @test
     *
     * Test the bill method charges the yearly price for a subscription
     */
    public function bill_charges_for_annual_subscriptions()
    {
        $sign = Service::where('name', 'CataLex Sign')->first();
        $user = $this->createUserWithBilling([], ['period' => 'annually'], [$sign->id]);

        $user->bill();

        $this->assertEquals($user->amountRequested, '60.00');
    }
}
