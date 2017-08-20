<?php

use App\BillingItem;
use App\Service;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BillableTrait_subscriptionUpToDate_Test extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function noChargeLogs()
    {
        $user = $this->createUser();

        $subscriptionUpToDate = $user->subscriptionUpToDate();
        $this->assertTrue($subscriptionUpToDate);
    }

    /**
     * @test
     */
    public function onlyChargeSuccessful()
    {
        // Create user and login
        $gcService = Service::where('name', 'Good Companies')->first();
        $user = $this->createUserWithBilling([], [], [$gcService->id]);

        $this->createChargeLogs($user, $gcService->id);

        $subscriptionUpToDate = $user->subscriptionUpToDate();
        $this->assertTrue($subscriptionUpToDate);
    }

    /**
     * @test
     */
    public function onlyChargePending()
    {
        // Create user and login
        $gcService = Service::where('name', 'Good Companies')->first();
        $user = $this->createUserWithBilling([], [], [$gcService->id]);

        $this->createChargeLogs($user, $gcService->id);

        // Change the charge log to pending
        $user->chargeLogs()->first()->update(['success' => false, 'pending' => true]); // when a charge log is pending, it is not yet successful, therefore: set success to false

        $subscriptionUpToDate = $user->subscriptionUpToDate();
        $this->assertTrue($subscriptionUpToDate);
    }

    /**
     * @test
     */
    public function onlyChargeFailed()
    {
        // Create user and login
        $gcService = Service::where('name', 'Good Companies')->first();
        $user = $this->createUserWithBilling([], [], [$gcService->id]);

        $this->createChargeLogs($user, $gcService->id);

        // Change the charge log to failed
        $user->chargeLogs()->first()->update(['success' => false]);

        $subscriptionUpToDate = $user->subscriptionUpToDate();
        $this->assertFalse($subscriptionUpToDate);
    }

    /**
     * @test
     */
    public function multipleCharges_useMostRecentCharge()
    {
        // Create user and login
        $gcService = Service::where('name', 'Good Companies')->first();
        $user = $this->createUserWithBilling([], [], [$gcService->id]);

        $this->createChargeLogs($user, $gcService->id, 2);

        // Test when oldest charge is successful, but most recent failed
        $user->chargeLogs()->orderBy('timestamp', 'DESC')->get()->all()[0]->update(['success' => false]);

        $subscriptionUpToDate = $user->subscriptionUpToDate();
        $this->assertFalse($subscriptionUpToDate);


        // Test when oldest charge is failed, but most recent is successful
        $user->chargeLogs()->orderBy('timestamp', 'DESC')->get()->all()[0]->update(['success' => true]);
        $user->chargeLogs()->orderBy('timestamp', 'DESC')->get()->all()[1]->update(['success' => false]);

        $subscriptionUpToDate = $user->subscriptionUpToDate();
        $this->assertTrue($subscriptionUpToDate);
    }

    private function createChargeLogs($user, $serviceId, $numberToCreate = 1)
    {
        BillingItem::create(['user_id' => $user->id, 'item_id' => 1, 'json_data' => json_encode(['company_name' => 'test company 1']), 'active' => true, 'service_id' => $serviceId, 'item_type' => 'gc_company']);
        BillingItem::create(['user_id' => $user->id, 'item_id' => 2, 'json_data' => json_encode(['company_name' => 'test company 2']), 'active' => true, 'service_id' => $serviceId, 'item_type' => 'gc_company']);
        BillingItem::create(['user_id' => $user->id, 'item_id' => 3, 'json_data' => json_encode(['company_name' => 'test company 3']), 'active' => true, 'service_id' => $serviceId, 'item_type' => 'gc_company']);

        // Bill the user to create charge logs
        foreach (range(1, $numberToCreate) as $index) {
            $user->bill();
            Carbon::setTestNow(Carbon::now()->addMonths(1));
        }
    }
}
