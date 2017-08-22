<?php

use App\Library\Billing;
use App\Service;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Stub\SyncCommand;

class LongPeriodBillingTest extends TestCase
{
    use DatabaseTransactions;

    const MONTHS_IN_SIMULATION = 25;
    const FULL_YEARS_IN_SIMULATION = 2; // Number of years rounded down
    const PART_YEARS_IN_SIMULATION = 3; // Number of years rounded up
    const MONTHLY_PRICE = '1.50';
    const YEARLY_PRICE = '12.00';

    private function massGenerateCompanies($user, $numberOfCompanies)
    {
        $fakeCompanies = [];

        for ($i = 0; $i < $numberOfCompanies; $i++) {
            $fakeCompanies[] = [
                'userId'      => $user->id,
                'companyId'   => 72770 + $i,
                'active'      => true,
                'companyName' => 'Test Company ' . $i,
            ];
        }

        return $fakeCompanies;
    }

    /**
     * @test
     */
    public function monthly_billing_for_one_user_with_one_company()
    {
        Carbon::setTestNow(Carbon::parse('25 March 2017'));

        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION);

        $gc = Service::where('name', 'Good Companies')->first();
        $user = $this->createUserWithBilling([], [], [$gc->id]);

        $fakeCompanies = [
            ['userId' => $user->id, 'companyId' => 72773, 'active' => true, 'companyName' => 'Johnny Mate'],
        ];

        $lastBilled = null;

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $lastBilled = Carbon::today();

                $this->assertEquals($user->paymentLastRequested, $lastBilled);
                $this->assertEquals($user->amountLastRequested, self::MONTHLY_PRICE);
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check the number of charge logs matches the number of times we tried to bill them
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(self::MONTHS_IN_SIMULATION, $numberOfChargeLogs);

        // Check the total amount billed is what we think it should be (number of months * monthly price)
        $expectedTotal = self::MONTHS_IN_SIMULATION * self::MONTHLY_PRICE;
        $this->assertEquals($expectedTotal, $user->totalEverRequested);
    }

    /**
     * @test
     */
    public function monthly_billing_for_one_user_with_multiple_companies()
    {
        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION);

        $gc = Service::where('name', 'Good Companies')->first();
        $user = $this->createUserWithBilling([], [], [$gc->id]);

        $numberOfCompanies = 3;
        $fakeCompanies = $this->massGenerateCompanies($user, $numberOfCompanies);

        $lastBilled = null;

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $lastBilled = Carbon::today();

                $this->assertEquals($user->paymentLastRequested, $lastBilled);
                $this->assertEquals($user->amountLastRequested, self::MONTHLY_PRICE * $numberOfCompanies);
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check the number of charge logs matches the number of times we tried to bill them
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(self::MONTHS_IN_SIMULATION, $numberOfChargeLogs);

        // Check the total amount billed is what we think it should be (number of months * monthly price * number of companies)
        $expectedTotal = self::MONTHS_IN_SIMULATION * self::MONTHLY_PRICE * $numberOfCompanies;
        $this->assertEquals($expectedTotal, $user->totalEverRequested);
    }

    /**
     * @test
     */
    public function monthly_billing_for_one_user_with_no_companies()
    {
        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION);
        $billingDetails = $this->createBillingDetails();
        $user = $this->createUser(['name' => 'User #1', 'email' => 'paddy+user1@catalex.nz', 'billing_detail_id' => $billingDetails->id]);
        $user->services()->attach(Service::where('name', 'Good Companies')->first());

        $fakeCompanies = [];

        $lastBilled = null;

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $lastBilled = Carbon::today();

                $this->assertEquals($user->paymentLastRequested, $lastBilled);
                $this->assertEquals($user->amountLastRequested, self::MONTHLY_PRICE * count($fakeCompanies));
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check no charge logs were created
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(0, $numberOfChargeLogs);

        // Check the total amount billed is what we think it should be (number of months * monthly price * number of companies)
        $expectedTotal = self::MONTHS_IN_SIMULATION * self::MONTHLY_PRICE * count($fakeCompanies);
        $this->assertEquals($expectedTotal, $user->totalEverRequested);
    }

    /**
     * @test
     */
    public function monthly_billing_for_one_user_with_one_company_billing_day_31st()
    {
        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION);
        $billingDetails = $this->createBillingDetails(['billing_day' => 31]);
        $user = $this->createUser(['name' => 'User #1', 'email' => 'paddy+user1@catalex.nz', 'billing_detail_id' => $billingDetails->id]);
        $user->services()->attach(Service::where('name', 'Good Companies')->first());

        $fakeCompanies = [
            ['userId' => $user->id, 'companyId' => 72773, 'active' => true, 'companyName' => 'Johnny Mate'],
        ];

        $lastBilled = null;

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $lastBilled = Carbon::today();

                $this->assertEquals($user->paymentLastRequested, $lastBilled);
                $this->assertEquals($user->amountLastRequested, self::MONTHLY_PRICE);
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check the number of charge logs matches the number of times we tried to bill them
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(self::MONTHS_IN_SIMULATION, $numberOfChargeLogs);

        // Check the total amount billed is what we think it should be (number of months * monthly price)
        $expectedTotal = self::MONTHS_IN_SIMULATION * self::MONTHLY_PRICE;
        $this->assertEquals($expectedTotal, $user->totalEverRequested);
    }

    /**
     * @test
     */
    public function yearly_billing_for_one_user_with_one_company()
    {
        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION);
        $billingDetails = $this->createBillingDetails(['period' => 'annually']);
        $user = $this->createUser(['name' => 'User #1', 'email' => 'paddy+user1@catalex.nz', 'billing_detail_id' => $billingDetails->id]);
        $user->services()->attach(Service::where('name', 'Good Companies')->first());

        $fakeCompanies = [
            ['userId' => $user->id, 'companyId' => 72773, 'active' => true, 'companyName' => 'Johnny Mate'],
        ];

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $this->assertEquals($user->paymentLastRequested, Carbon::today());

                if ($user->amountLastRequested > 0) {
                    $this->assertEquals($user->amountLastRequested, self::YEARLY_PRICE);
                }
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check the user was charged the correct number of times
        $this->assertEquals(self::PART_YEARS_IN_SIMULATION, $user->timesBilled);

        // Check the number of charge logs matches the number of times we tried to bill them
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(self::PART_YEARS_IN_SIMULATION, $numberOfChargeLogs);

        // Check the total amount billed is what we think it should be (number of years * yearly price)
        $expectedTotal = self::PART_YEARS_IN_SIMULATION * self::YEARLY_PRICE;
        $this->assertEquals($expectedTotal, $user->totalEverRequested);
    }

    /**
     * @test
     */
    public function yearly_billing_for_one_user_with_multiple_companies()
    {
        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION);
        $billingDetails = $this->createBillingDetails(['period' => 'annually']);
        $user = $this->createUser(['name' => 'User #1', 'email' => 'paddy+user1@catalex.nz', 'billing_detail_id' => $billingDetails->id]);
        $user->services()->attach(Service::where('name', 'Good Companies')->first());

        $numberOfCompanies = 3;
        $fakeCompanies = $this->massGenerateCompanies($user, $numberOfCompanies);

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $this->assertEquals($user->paymentLastRequested, Carbon::today());

                if ($user->amountLastRequested > 0) {
                    $this->assertEquals($user->amountLastRequested, self::YEARLY_PRICE * $numberOfCompanies);
                }
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check the user was charged the correct number of times
        $this->assertEquals(self::PART_YEARS_IN_SIMULATION, $user->timesBilled);

        // Check the number of charge logs matches the number of times we tried to bill them
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(self::PART_YEARS_IN_SIMULATION, $numberOfChargeLogs);

        // Check the total amount billed is what we think it should be (number of years * yearly price)
        $expectedTotal = self::PART_YEARS_IN_SIMULATION * self::YEARLY_PRICE * $numberOfCompanies;
        $this->assertEquals($expectedTotal, $user->totalEverRequested);
    }

    /**
     * @test
     */
    public function yearly_billing_for_one_user_with_no_companies()
    {
        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION);
        $billingDetails = $this->createBillingDetails(['period' => 'annually']);
        $user = $this->createUser(['name' => 'User #1', 'email' => 'paddy+user1@catalex.nz', 'billing_detail_id' => $billingDetails->id]);
        $user->services()->attach(Service::where('name', 'Good Companies')->first());

        $numberOfCompanies = 0;
        $fakeCompanies = $this->massGenerateCompanies($user, $numberOfCompanies);

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $this->assertEquals($user->paymentLastRequested, Carbon::today());

                if ($user->amountLastRequested > 0) {
                    $this->assertEquals($user->amountLastRequested, self::YEARLY_PRICE * $numberOfCompanies);
                }
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check the user was charged the correct number of times
        $this->assertEquals(0, $user->timesBilled);

        // Check the number of charge logs matches the number of times we tried to bill them
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(0, $numberOfChargeLogs);

        // Check the total amount billed is what we think it should be (number of years * yearly price)
        $expectedTotal = self::PART_YEARS_IN_SIMULATION * self::YEARLY_PRICE * $numberOfCompanies;
        $this->assertEquals($expectedTotal, $user->totalEverRequested);
    }

    /**
     * @test
     */
    public function yearly_billing_for_one_user_billing_day_31st()
    {
        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION);
        $billingDetails = $this->createBillingDetails(['period' => 'annually', 'billing_day' => 31]);
        $user = $this->createUser(['name' => 'User #1', 'email' => 'paddy+user1@catalex.nz', 'billing_detail_id' => $billingDetails->id]);
        $user->services()->attach(Service::where('name', 'Good Companies')->first());

        $numberOfCompanies = 0;
        $fakeCompanies = $this->massGenerateCompanies($user, $numberOfCompanies);

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $this->assertEquals($user->paymentLastRequested, Carbon::today());

                if ($user->amountLastRequested > 0) {
                    $this->assertEquals($user->amountLastRequested, self::YEARLY_PRICE * $numberOfCompanies);
                }
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check the user was charged the correct number of times
        $this->assertEquals(0, $user->timesBilled);

        // Check the number of charge logs matches the number of times we tried to bill them
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(0, $numberOfChargeLogs);

        // Check the total amount billed is what we think it should be (number of years * yearly price)
        $expectedTotal = self::PART_YEARS_IN_SIMULATION * self::YEARLY_PRICE * $numberOfCompanies;
        $this->assertEquals($expectedTotal, $user->totalEverRequested);
    }

    /**
     * @test
     */
    public function monthly_billing_for_free_user_with_one_company()
    {
        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION);
        $billingDetails = $this->createBillingDetails();
        $user = $this->createUser(['name' => 'User #1', 'email' => 'paddy+user1@catalex.nz', 'billing_detail_id' => $billingDetails->id, 'free' => true]);
        $user->services()->attach(Service::where('name', 'Good Companies')->first());

        $fakeCompanies = [
            ['userId' => $user->id, 'companyId' => 72773, 'active' => true, 'companyName' => 'Johnny Mate'],
        ];

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $this->assertEquals($user->paymentLastRequested, Carbon::today());
                $this->assertEquals($user->amountLastRequested, self::MONTHLY_PRICE);
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check no charge logs were created
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(0, $numberOfChargeLogs);

        //heck nothing was charged
        $expectedTotal = 0;
        $this->assertEquals($expectedTotal, $user->totalEverRequested);
    }

    /**
     * @test
     */
    public function monthly_billing_for_free_user_with_no_companies()
    {
        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION);
        $billingDetails = $this->createBillingDetails();
        $user = $this->createUser(['name' => 'User #1', 'email' => 'paddy+user1@catalex.nz', 'billing_detail_id' => $billingDetails->id, 'free' => true]);
        $user->services()->attach(Service::where('name', 'Good Companies')->first());

        $fakeCompanies = [];

        $lastBilled = null;

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $lastBilled = Carbon::today();

                $this->assertEquals($user->paymentLastRequested, $lastBilled);
                $this->assertEquals($user->amountLastRequested, 0);
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check no charge logs were created
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(0, $numberOfChargeLogs);

        // Check the user was not charged
        $expectedTotal = 0;
        $this->assertEquals($expectedTotal, $user->totalEverRequested);
    }

    /**
     *
     * ##########
     *
     * Discounts
     *
     * ##########
     *
     */

    /**
     * @test
     */
    public function monthly_billing_for_one_user_with_multiple_companies_15_percent_discount()
    {
        $discountPercent = '15';

        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION);
        $billingDetails = $this->createBillingDetails(['discount_percent' => '15']);
        $user = $this->createUser(['name' => 'User #1', 'email' => 'paddy+user1@catalex.nz', 'billing_detail_id' => $billingDetails->id]);
        $user->services()->attach(Service::where('name', 'Good Companies')->first());

        $numberOfCompanies = 3;
        $fakeCompanies = $this->massGenerateCompanies($user, $numberOfCompanies);

        $lastBilled = null;

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $lastBilled = Carbon::today();

                $this->assertEquals($user->paymentLastRequested, $lastBilled);
                $expectedAmountBeforeDiscount = self::MONTHLY_PRICE * $numberOfCompanies;
                $expectedAmountAfterDiscount = Billing::applyDiscount($expectedAmountBeforeDiscount, $discountPercent);
                $this->assertEquals($expectedAmountAfterDiscount, $user->amountLastRequested);
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check the number of charge logs matches the number of times we tried to bill them
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(self::MONTHS_IN_SIMULATION, $numberOfChargeLogs);

        // Check the total amount billed is what we think it should be (number of months * monthly price * number of companies)
        $expectedAmount = self::MONTHS_IN_SIMULATION * Billing::applyDiscount(self::MONTHLY_PRICE * $numberOfCompanies, $discountPercent);
        $this->assertEquals($expectedAmount, $user->totalEverRequested);
    }
}
