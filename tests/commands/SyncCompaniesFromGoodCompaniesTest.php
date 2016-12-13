<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Stub\SyncCommand;
use App\User;
use App\BillingItem;
use App\Service;

/**
 * Run the tests on the above Sync Command with the stubbed out API request and logging
 */
class SyncCompaniesFromGoodCompaniesTest extends TestCase
{
    use DatabaseTransactions;

    protected $seeder = 'DatabaseSeeder';

    private function buildJsonData($company)
    {
        return json_encode(['company_name' => $company['companyName']]);
    }

    /**
     * @test
     *
     * With no current Billing Items, import companies from GC.
     * Make sure a billing item is made for each of them
     */
    public function syncWithNoExistingBillingItems()
    {
        // Create out fake users
        $user1 = User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => bcrypt('password'), 'active' => true]);
        $user2 = User::create(['name' => 'User 2', 'email' => 'user2@example.com', 'password' => bcrypt('password'), 'active' => true]);

        // Define our fake companies
        $fakeCompanies = [
            ['userId' => $user1->id, 'companyId' => 72773, 'active' => true, 'companyName' => 'Johnny Mate'],
            ['userId' => $user1->id, 'companyId' => 87377, 'active' => false, 'companyName' => 'Jimmy Likes Pies'],
            ['userId' => $user2->id, 'companyId' => 10100, 'active' => true, 'companyName' => 'Emma Does Work'],
        ];

        // Create the sync command object and hand it our fake companies
        $syncCommand = new SyncCommand();
        $syncCommand->fakeCompanies = $fakeCompanies;

        // Run the sync command
        $syncCommand->handle();

        // Get the created billing items
        $billingItems = BillingItem::get();

        // Check the correct number of billing items were created
        $this->assertEquals($billingItems->count(), count($fakeCompanies));

        foreach ($fakeCompanies as $index => $company) {
            // Check this company's billing item was created for the correct user
            $this->assertEquals($billingItems[$index]->user_id, $fakeCompanies[$index]['userId']);

            // Check the Company ID was placed in the 
            $this->assertEquals($billingItems[$index]->item_id, $fakeCompanies[$index]['companyId']);

            // Check the active flag was set correctly
            $this->assertEquals($billingItems[$index]->active, $fakeCompanies[$index]['active']);

            // Check the extra data about the billing item was created, jsonified, and stored correctly
            $expectedJson = $this->buildJsonData($fakeCompanies[$index]);
            $this->assertEquals($billingItems[$index]->json_data, $expectedJson);
        }
    }

    /**
     * @test
     *
     * If there is a Billing Item in the database matches one of the import companies;
     * make sure a duplicate is not made.
     */
    public function syncWithExistingDuplicateBillingItems()
    {
        // Create out fake users
        $user1 = User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => bcrypt('password'), 'active' => true]);
        $user2 = User::create(['name' => 'User 2', 'email' => 'user2@example.com', 'password' => bcrypt('password'), 'active' => true]);

        // Define our fake companies
        $fakeCompanies = [
            ['userId' => $user1->id, 'companyId' => 72773, 'active' => true, 'companyName' => 'Johnny Mate'],
            ['userId' => $user1->id, 'companyId' => 87377, 'active' => false, 'companyName' => 'Jimmy Likes Pies'],
            ['userId' => $user2->id, 'companyId' => 10100, 'active' => true, 'companyName' => 'Emma Does Work'],
        ];

        // Create a billing item that matches one of the fake companies
        $billingItem = new BillingItem([
            'item_id' => $fakeCompanies[0]['companyId'],
            'item_type' => BillingItem::ITEM_TYPE_GC_COMPANY,
            'json_data' => $this->buildJsonData($fakeCompanies[0]),
            'active' => $fakeCompanies[0]['active'],
        ]);

        $billingItem->user_id = $fakeCompanies[0]['userId'];
        $billingItem->service_id = Service::where('name', '=', 'Good Companies')->first()->id;
        $billingItem->save();

        // Create the sync command object and hand it our fake companies
        $syncCommand = new SyncCommand();
        $syncCommand->fakeCompanies = $fakeCompanies;

        // Run the sync command
        $syncCommand->handle();

        // Get the created billing items
        $billingItems = BillingItem::get();

        // Check the correct number of billing items were created
        $this->assertEquals($billingItems->count(), count($fakeCompanies));

        foreach ($fakeCompanies as $index => $company) {
            // Check this company's billing item was created for the correct user
            $this->assertEquals($billingItems[$index]->user_id, $fakeCompanies[$index]['userId']);

            // Check the Company ID was placed in the 
            $this->assertEquals($billingItems[$index]->item_id, $fakeCompanies[$index]['companyId']);

            // Check the active flag was set correctly
            $this->assertEquals($billingItems[$index]->active, $fakeCompanies[$index]['active']);

            // Check the extra data about the billing item was created, jsonified, and stored correctly
            $expectedJson = $this->buildJsonData($fakeCompanies[$index]);
            $this->assertEquals($billingItems[$index]->json_data, $expectedJson);
        }
    }

    /**
     * @test
     *
     * If there is a Billing Item in the database matches one of the import companies,
     * but with inconsistent details (maybe something was updated on GC); make sure
     * the existing Billing Items details are updated no duplicate is not created
     */
    public function syncWithExistingBillingItemsWithInconsistentData()
    {
        // Create out fake users
        $user1 = User::create(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => bcrypt('password'), 'active' => true]);
        $user2 = User::create(['name' => 'User 2', 'email' => 'user2@example.com', 'password' => bcrypt('password'), 'active' => true]);

        // Define our fake companies
        $fakeCompanies = [
            ['userId' => $user1->id, 'companyId' => 72773, 'active' => true, 'companyName' => 'Johnny Mate'],
            ['userId' => $user1->id, 'companyId' => 87377, 'active' => false, 'companyName' => 'Jimmy Likes Pies'],
            ['userId' => $user2->id, 'companyId' => 10100, 'active' => true, 'companyName' => 'Emma Does Work'],
        ];

        // Create a billing item that matches one of the fake companies, but with
        // inconsistent data.
        // NOTE: item_id and item_type must match as we use them to match companies
        // in Good Companies to Billing Items
        $billingItem = new BillingItem([
            'item_id' => $fakeCompanies[0]['companyId'],
            'item_type' => BillingItem::ITEM_TYPE_GC_COMPANY,
            'json_data' => '{"testing": {}}',
            'active' => !$fakeCompanies[0]['active'], // reverse the companies active boolean
        ]);

        $billingItem->user_id = $fakeCompanies[0]['userId'];
        $billingItem->service_id = Service::where('name', '=', 'Good Companies')->first()->id;
        $billingItem->save();

        // Create the sync command object and hand it our fake companies
        $syncCommand = new SyncCommand();
        $syncCommand->fakeCompanies = $fakeCompanies;

        // Run the sync command
        $syncCommand->handle();

        // Get the created billing items
        $billingItems = BillingItem::get();

        // Check the correct number of billing items were created
        $this->assertEquals($billingItems->count(), count($fakeCompanies));

        foreach ($fakeCompanies as $index => $company) {
            // Check this company's billing item was created for the correct user
            $this->assertEquals($billingItems[$index]->user_id, $fakeCompanies[$index]['userId']);

            // Check the Company ID was placed in the 
            $this->assertEquals($billingItems[$index]->item_id, $fakeCompanies[$index]['companyId']);

            // Check the active flag was set correctly
            $this->assertEquals($billingItems[$index]->active, $fakeCompanies[$index]['active']);

            // Check the extra data about the billing item was created, jsonified, and stored correctly
            $expectedJson = $this->buildJsonData($fakeCompanies[$index]);
            $this->assertEquals($billingItems[$index]->json_data, $expectedJson);
        }
    }
}
