<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\BillingDetail;
use App\BillingItem;
use App\BillingItemPayment;
use App\Service;
use Carbon\Carbon;

class User extends \App\User
{
    public $totalDollarsDue;

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

class BillableTraitTest extends TestCase
{
    use DatabaseTransactions;

    protected $seeder = 'DatabaseSeeder';

    private function createService($name, $paid=true)
    {
        return Service::create([
            'name' => $name,
            'is_paid_service' => $paid,
        ]);
    }

    private function createBillingDetails($period=null, $billingDay=null)
    {
        return BillingDetail::create([
            'period' => $period ? : 'annually',
            'billing_day' => $billingDay ? : Carbon::today()->day,
        ]);
    }

    private function createBillingItem($data)
    {
        return BillingItem::create([
            'user_id' => $data['user_id'],
            'service_id' => $data['service_id'],
            'item_id' => $data['item_id'],
            'item_type' => in_array('item_type', $data) ? $data['item_type'] : 'gc_company',
            'json_data' => in_array('json_data', $data) ? $data['json_data'] : '{}',
            'active' => in_array('active', $data) ? $data['active'] : true,
        ]);
    }

    private function massCreateBillingItems($userId, $serviceId, $numberOfItems)
    {
        $billingItems = [];

        for ($index = 0; $index < $numberOfItems; $index++) {
            $billingItems[] = $this->createBillingItem([
                'user_id' => $userId,
                'service_id' => $serviceId,
                'item_id' => $index,
            ]);
        }

        return $billingItems;
    }

    /**
     * @test
     */
    public function bill_user_annually()
    {
        // Create a user
        $billingDetails = $this->createBillingDetails();
        $user = User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => bcrypt('password'), 'active' => true, 'billing_detail_id' => $billingDetails->id]);

        // Create a service and attach it to the user
        $service = $this->createService('Good Companies');
        $user->services()->attach($service);

        // Create a billing item
        $billingItem1 = BillingItem::create(['user_id' => $user->id, 'service_id' => $service->id, 'item_id' => 1, 'item_type' => 'gc_company', 'json_data' => '{}', 'active' => true]);

        // Bill the user
        $user->bill();

        // Check the result
        $actual = $user->totalDollarsDue;
        $expected = '12.00';
        
        $this->assertEquals($actual, $expected);
    }

    /**
     * @test
     */
    public function bill_user_monthly()
    {
        // Create a user
        $billingDetails = $this->createBillingDetails('monthly');
        $user = User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => bcrypt('password'), 'active' => true, 'billing_detail_id' => $billingDetails->id]);

        // Create a service and attach it to the user
        $service = $this->createService('Good Companies');
        $user->services()->attach($service);

        // Create a billing item
        $billingItem1 = BillingItem::create(['user_id' => $user->id, 'service_id' => $service->id, 'item_id' => 1, 'item_type' => 'gc_company', 'json_data' => '{}', 'active' => true]);

        // Bill the user
        $user->bill();

        // Check the result
        $actual = $user->totalDollarsDue;
        $expected = '1.50';

        $this->assertEquals($actual, $expected);
    }

    /**
     * @test
     */
    public function bill_user_annually_noBillingItems()
    {
        // Create a user
        $billingDetails = $this->createBillingDetails();
        $user = User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => bcrypt('password'), 'active' => true, 'billing_detail_id' => $billingDetails->id]);

        // Create a service and attach it to the user
        $service = $this->createService('Good Companies');
        $user->services()->attach($service);

        // Bill the user
        $user->bill();

        // Check the result
        $actual = $user->totalDollarsDue;
        $expected = '0.00';

        $this->assertEquals($actual, $expected);
    }

    /**
     * @test
     */
    public function bill_user_monthly_noBillingItems()
    {
        // Create a user
        $billingDetails = $this->createBillingDetails('monthly');
        $user = User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => bcrypt('password'), 'active' => true, 'billing_detail_id' => $billingDetails->id]);

        // Create a service and attach it to the user
        $service = $this->createService('Good Companies');
        $user->services()->attach($service);

        // Bill the user
        $user->bill();

        // Check the result
        $actual = $user->totalDollarsDue;
        $expected = '0.00';

        $this->assertEquals($actual, $expected);
    }

    /**
     * @test
     */
    public function bill_user_annually_twoBillingItems()
    {
        // Create a user
        $billingDetails = $this->createBillingDetails();
        $user = User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => bcrypt('password'), 'active' => true, 'billing_detail_id' => $billingDetails->id]);

        // Create a service and attach it to the user
        $service = $this->createService('Good Companies');
        $user->services()->attach($service);

        // Create a billing items
        $billingItem1 = BillingItem::create(['user_id' => $user->id, 'service_id' => $service->id, 'item_id' => 1, 'item_type' => 'gc_company', 'json_data' => '{}', 'active' => true]);
        $billingItem2 = BillingItem::create(['user_id' => $user->id, 'service_id' => $service->id, 'item_id' => 2, 'item_type' => 'gc_company', 'json_data' => '{}', 'active' => true]);

        // Bill the user
        $user->bill();

        // Check the result
        $actual = $user->totalDollarsDue;
        $expected = '24.00';

        $this->assertEquals($actual, $expected);
    }

    /**
     * @test
     */
    public function bill_user_monthly_twoBillingItems()
    {
        // Create a user
        $billingDetails = $this->createBillingDetails('monthly');
        $user = User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => bcrypt('password'), 'active' => true, 'billing_detail_id' => $billingDetails->id]);

        // Create a service and attach it to the user
        $service = $this->createService('Good Companies');
        $user->services()->attach($service);

        // Create a billing items
        $billingItem1 = BillingItem::create(['user_id' => $user->id, 'service_id' => $service->id, 'item_id' => 1, 'item_type' => 'gc_company', 'json_data' => '{}', 'active' => true]);
        $billingItem2 = BillingItem::create(['user_id' => $user->id, 'service_id' => $service->id, 'item_id' => 2, 'item_type' => 'gc_company', 'json_data' => '{}', 'active' => true]);

        // Bill the user
        $user->bill();

        // Check the result
        $actual = $user->totalDollarsDue;
        $expected = '3.00';

        $this->assertEquals($actual, $expected);
    }

    /**
     * @test
     */
    public function bill_user_annually_lotsOfBillingItems()
    {
        // Create a user
        $billingDetails = $this->createBillingDetails();
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

        $this->assertEquals($actual, $expected);
    }

    /**
     * @test
     */
    public function bill_user_monthly_lotsOfBillingItems()
    {
        // Create a user
        $billingDetails = $this->createBillingDetails('monthly');
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
        $expected = '4674.00';

        $this->assertEquals($actual, $expected);
    }

    /**
     * @test
     */
    public function bill_organisation_oneUser()
    {
        // Create the organisation
        $billingDetails = $this->createBillingDetails('monthly');
        $organisation = Organisation::create(['name' => 'Org 1', 'billing_detail_id' => $billingDetails->id]);

        // Create a few users
        $user1 = User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => bcrypt('password'), 'active' => true]);

        // Create a service and attach it to the user
        $service = $this->createService('Good Companies');
        $user1->services()->attach($service);

        // Create a billing item
        $billingItem1 = BillingItem::create(['user_id' => $user1->id, 'service_id' => $service->id, 'item_id' => 1, 'item_type' => 'gc_company', 'json_data' => '{}', 'active' => true]);

        // Bill the user
        $user1->bill();

        // Check the result
        $actual = $user1->totalDollarsDue;
        $expected = '12.00';
        
        $this->assertEquals($actual, $expected);
    }
}
