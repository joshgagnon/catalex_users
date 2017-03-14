<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\BillingDetail;
use App\BillingItem;
use App\BillingItemPayment;
use App\Service;
use Carbon\Carbon;
use Illuminate\Database\QueryException;

class User extends \App\User
{
    public $totalDollarsDue;

    public function sendInvoices($type, $invoiceNumber, $listItems, $totalAmount, $gst, $orgName=null, $orgId=null)
    {
        // Do nothing
    }

    protected function requestPayment($totalDollarsDue)
    {
        $this->totalDollarsDue = $totalDollarsDue;
        return true; // Don't do any payment stuff, just pretend it worked
    }
}

class Organisation extends \App\Organisation
{
    public $totalDollarsDue;

    protected function requestPayment($totalDollarsDue)
    {
        $this->totalDollarsDue = $totalDollarsDue;
        return true; // Don't do any payment stuff, just pretend it worked
    }
}

class BillableTraitBillMethodTest extends TestCase
{
    use DatabaseTransactions;

    protected $seeder = 'DatabaseSeeder';

    private function createService($name, $paid=null)
    {
        return Service::create([
            'name' => $name,
            'is_paid_service' => $paid ? : true,
        ]);
    }

    private function createBillingItem($data)
    {
        return BillingItem::create([
            'user_id' => $data['user_id'],
            'service_id' => $data['service_id'],
            'item_id' => $data['item_id'],
            'item_type' => array_key_exists('item_type', $data) ? $data['item_type'] : 'gc_company',
            'json_data' => array_key_exists('json_data', $data) ? $data['json_data'] : '{\"company_name\": \"test\"}',
            'active' => array_key_exists('active', $data) ? $data['active'] : true,
        ]);
    }

    private function massCreateBillingItems($userId, $serviceId, $numberOfItems)
    {
        $billingItems = [];

        for ($index = 0; $index < $numberOfItems; $index++) {
            $itemId = uniqid();

            while (BillingItem::where('item_id', $itemId)->exists()) {
                $itemId = uniqid();
            }

            $billingItems[] = $this->createBillingItem([
                'user_id' => $userId,
                'service_id' => $serviceId,
                'item_id' => $itemId,
            ]);
        }

        return $billingItems;
    }

    private function massCreateUser($numberOfUsers, $organisationId=null)
    {
        $users = [];

        for ($index = 0; $index < $numberOfUsers; $index++) {
            $users[] = User::create([
                'name' => 'User ' . $index,
                'email' => 'user' . $index . '@example.com',
                'password' => bcrypt('password'),
                'active' => true,
                'organisation_id' => $organisationId,
            ]);
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
        $billingItem1 = BillingItem::create(['user_id' => $user->id, 'service_id' => $service->id, 'item_id' => 1, 'item_type' => 'gc_company', 'json_data' => '{\"company_name\":\"test\"}', 'active' => true]);

        // Bill the user
        $billingResult = $user->bill();
        $this->assertFalse($billingResult);

        // Check the user wasn't billed
        $this->assertNull($user->totalDollarsDue);
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
        $billingItem1 = BillingItem::create(['user_id' => $user->id, 'service_id' => $service->id, 'item_id' => 1, 'item_type' => 'gc_company', 'json_data' => '{\"company_name\":\"test\"}', 'active' => true]);

        // Bill the user
        $user->bill();

        // Check the result
        $actual = $user->totalDollarsDue;
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
        $billingItem1 = BillingItem::create(['user_id' => $user->id, 'service_id' => $service->id, 'item_id' => 1, 'item_type' => 'gc_company', 'json_data' => '{\"company_name\":\"test\"}', 'active' => true]);

        // Bill the user
        $user->bill();

        // Check the result
        $actual = $user->totalDollarsDue;
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
        $actual = $user->totalDollarsDue;
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
        $actual = $user->totalDollarsDue;
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

        // Create a billing items
        $billingItem1 = BillingItem::create(['user_id' => $user->id, 'service_id' => $service->id, 'item_id' => 1, 'item_type' => 'gc_company', 'json_data' => '{\"company_name\":\"test\"}', 'active' => true]);
        $billingItem2 = BillingItem::create(['user_id' => $user->id, 'service_id' => $service->id, 'item_id' => 2, 'item_type' => 'gc_company', 'json_data' => '{\"company_name\":\"test\"}', 'active' => true]);

        // Bill the user
        $user->bill();

        // Check the result
        $actual = $user->totalDollarsDue;
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

        // Create a billing items
        $billingItem1 = BillingItem::create(['user_id' => $user->id, 'service_id' => $service->id, 'item_id' => 1, 'item_type' => 'gc_company', 'json_data' => '{\"company_name\":\"test\"}', 'active' => true]);
        $billingItem2 = BillingItem::create(['user_id' => $user->id, 'service_id' => $service->id, 'item_id' => 2, 'item_type' => 'gc_company', 'json_data' => '{\"company_name\":\"test\"}', 'active' => true]);

        // Bill the user
        $user->bill();

        // Check the result
        $actual = $user->totalDollarsDue;
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
        $numberOfBillingItems = 4674;
        $billingItems = [];
        $this->massCreateBillingItems($user->id, $service->id, $numberOfBillingItems);
        
        // Bill the user
        $user->bill();

        // Check the result
        $actual = $user->totalDollarsDue;
        $expected = '56088.00';

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
        $numberOfBillingItems = 4674;
        $billingItems = [];
        $this->massCreateBillingItems($user->id, $service->id, $numberOfBillingItems);

        // Bill the user
        $user->bill();

        // Check the result
        $expected = '7011.00';
        $actual = $user->totalDollarsDue;

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

        // Create a few users
        $user1 = User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
            'active' => true,
            'organisation_id' => $organisation->id,
        ]);

        // Create a service and attach it to the user
        $service = $this->createService('Good Companies');
        $user1->services()->attach($service);

        // Create a billing item
        $billingItem1 = BillingItem::create([
            'user_id' => $user1->id,
            'service_id' => $service->id,
            'item_id' => 1,
            'item_type' => 'gc_company',
            'json_data' => '{\"company_name\":\"test\"}',
            'active' => true,
        ]);

        // Bill the organisation
        $organisation->bill();

        // Check the result
        $actual = $organisation->totalDollarsDue;
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

        // Create a few users
        $user1 = User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
            'active' => true,
            'organisation_id' => $organisation->id,
        ]);

        // Create a service and attach it to the user
        $service = $this->createService('Good Companies');
        $user1->services()->attach($service);

        $numberOfBillingItems = 127;
        $billingItems = [];
        $this->massCreateBillingItems($user1->id, $service->id, $numberOfBillingItems);

        // Bill the organisation
        $organisation->bill();

        // Check the result
        $actual = $organisation->totalDollarsDue;
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
        $users = $this->massCreateUser(4, $organisation->id);

        // Create a service and attach it to the user
        $service = $this->createService('Good Companies');
        $organisation->services()->attach($service);

        // Give each user the 
        $billingItems = [];
        foreach ($users as $index => $user) {
            $billingItems[] = BillingItem::create([
                'user_id' => $user->id,
                'service_id' => $service->id,
                'item_id' => $index,
                'item_type' => 'gc_company',
                'json_data' => '{\"company_name\":\"test\"}',
                'active' => true,
            ]);
        }

        // Bill the organisation
        $organisation->bill();

        // Check the result
        $actual = $organisation->totalDollarsDue;
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
        $users = $this->massCreateUser($numberOfUsers, $organisation->id);

        // Create a service and attach it to the user
        $service = $this->createService('Good Companies');
        $organisation->services()->attach($service);

        // Give each user the 
        foreach ($users as $index => $user) {
            $this->massCreateBillingItems($user->id, $service->id, $numberOfItemsPerUser);
        }

        // Bill the organisation
        $organisation->bill();

        // Check the result
        $actual = $organisation->totalDollarsDue;
        $expected = '480.00'; // 4 users * 10 items per user * $12 a year
        
        $this->assertEquals($expected, $actual);
    }
}
