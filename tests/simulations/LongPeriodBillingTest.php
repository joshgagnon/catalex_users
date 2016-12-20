<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;
use Tests\Stub\SyncCommand;
use App\Service;

class LongPeriodBillingTest extends TestCase
{
    use DatabaseTransactions;

    const MONTHS_IN_SIMULATION = 25;
    const FULL_YEARS_IN_SIMULATION = 2; // Number of years rounded down
    const PART_YEARS_IN_SIMULATION = 3; // Number of years rounded up
    const MONTHLY_PRICE = '1.50';
    const YEARLY_PRICE = '12.00';

    protected $seeder = 'ServiceSeeder';

    private function massGenerateCompanies($user, $numberOfCompanies)
    {
        $fakeCompanies = [];

        for ($i = 0; $i < $numberOfCompanies; $i++) {
            $fakeCompanies[] = [
                'userId' => $user->id,
                'companyId' => 72770 + $i,
                'active' => true,
                'companyName' => 'Test Company ' . $i
            ];
        }

        return $fakeCompanies;
    }

    /**
     * @test
     */
    public function monthlyBilling_oneUser_oneCompany()
    {
        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION)->addDays(1);
        $billingDetails = $this->createBillingDetails();
        $user = $this->createUser(['name' => 'User #1', 'email' => 'paddy+user1@catalex.nz', 'billing_detail_id' => $billingDetails->id]);
        $user->services()->attach(Service::where('name', 'Good Companies')->first());

        $fakeCompanies = [
            ['userId' => $user->id, 'companyId' => 72773, 'active' => true, 'companyName' => 'Johnny Mate']
        ];

        $lastBilled = null;
        $totalAmountBilled = 0;

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $lastBilled = Carbon::today();

                $this->assertEquals($user->paymentLastRequested, $lastBilled);
                $this->assertEquals($user->amountRequested, self::MONTHLY_PRICE);

                $totalAmountBilled += $user->amountRequested;
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check the number of charge logs matches the number of times we tried to bill them
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(self::MONTHS_IN_SIMULATION, $numberOfChargeLogs);

        // Check the total amount billed is what we think it should be (number of months * monthly price)
        $expectedTotal = self::MONTHS_IN_SIMULATION * self::MONTHLY_PRICE;
        $this->assertEquals($expectedTotal, $totalAmountBilled);
    }

    /**
     * @test
     */
    public function monthlyBilling_oneUser_tenCompanies()
    {
        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION)->addDays(1);
        $billingDetails = $this->createBillingDetails();
        $user = $this->createUser(['name' => 'User #1', 'email' => 'paddy+user1@catalex.nz', 'billing_detail_id' => $billingDetails->id]);
        $user->services()->attach(Service::where('name', 'Good Companies')->first());

        $numberOfCompanies = 10;
        $fakeCompanies = $this->massGenerateCompanies($user, $numberOfCompanies);

        $lastBilled = null;
        $totalAmountBilled = 0;

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $lastBilled = Carbon::today();

                $this->assertEquals($user->paymentLastRequested, $lastBilled);
                $this->assertEquals($user->amountRequested, self::MONTHLY_PRICE * $numberOfCompanies);

                $totalAmountBilled += $user->amountRequested;
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check the number of charge logs matches the number of times we tried to bill them
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(self::MONTHS_IN_SIMULATION, $numberOfChargeLogs);

        // Check the total amount billed is what we think it should be (number of months * monthly price * number of companies)
        $expectedTotal = self::MONTHS_IN_SIMULATION * self::MONTHLY_PRICE * $numberOfCompanies;;
        $this->assertEquals($expectedTotal, $totalAmountBilled);
    }

    /**
     * @test
     */
    public function monthlyBilling_oneUser_noCompanies()
    {
        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION)->addDays(1);
        $billingDetails = $this->createBillingDetails();
        $user = $this->createUser(['name' => 'User #1', 'email' => 'paddy+user1@catalex.nz', 'billing_detail_id' => $billingDetails->id]);
        $user->services()->attach(Service::where('name', 'Good Companies')->first());

        $fakeCompanies = [];

        $lastBilled = null;
        $totalAmountBilled = 0;

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $lastBilled = Carbon::today();

                $this->assertEquals($user->paymentLastRequested, $lastBilled);
                $this->assertEquals($user->amountRequested, self::MONTHLY_PRICE * count($fakeCompanies));

                $totalAmountBilled += $user->amountRequested;
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check no charge logs were created
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(0, $numberOfChargeLogs);

        // Check the total amount billed is what we think it should be (number of months * monthly price * number of companies)
        $expectedTotal = self::MONTHS_IN_SIMULATION * self::MONTHLY_PRICE * count($fakeCompanies);
        $this->assertEquals($expectedTotal, $totalAmountBilled);
    }

    /**
     * @test
     */
    public function monthlyBilling_oneUser_oneCompany_billingDayIs31st()
    {
        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION)->addDays(1);
        $billingDetails = $this->createBillingDetails(['billing_day' => 31]);
        $user = $this->createUser(['name' => 'User #1', 'email' => 'paddy+user1@catalex.nz', 'billing_detail_id' => $billingDetails->id]);
        $user->services()->attach(Service::where('name', 'Good Companies')->first());

        $fakeCompanies = [
            ['userId' => $user->id, 'companyId' => 72773, 'active' => true, 'companyName' => 'Johnny Mate']
        ];

        $lastBilled = null;
        $totalAmountBilled = 0;

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $lastBilled = Carbon::today();


                $this->assertEquals($user->paymentLastRequested, $lastBilled);
                $this->assertEquals($user->amountRequested, self::MONTHLY_PRICE);

                $totalAmountBilled += $user->amountRequested;
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check the number of charge logs matches the number of times we tried to bill them
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(self::MONTHS_IN_SIMULATION, $numberOfChargeLogs);

        // Check the total amount billed is what we think it should be (number of months * monthly price)
        $expectedTotal = self::MONTHS_IN_SIMULATION * self::MONTHLY_PRICE;
        $this->assertEquals($expectedTotal, $totalAmountBilled);
    }

    /**
     * @test
     */
    public function yearlyBilling_oneUser_oneCompany()
    {
        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION)->addDays(1);
        $billingDetails = $this->createBillingDetails(['period' => 'annually']);
        $user = $this->createUser(['name' => 'User #1', 'email' => 'paddy+user1@catalex.nz', 'billing_detail_id' => $billingDetails->id]);
        $user->services()->attach(Service::where('name', 'Good Companies')->first());

        $fakeCompanies = [
            ['userId' => $user->id, 'companyId' => 72773, 'active' => true, 'companyName' => 'Johnny Mate']
        ];

        $totalAmountBilled = 0;
        $timesCharged = 0;

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $this->assertEquals($user->paymentLastRequested, Carbon::today());

                if ($user->amountRequested > 0) {
                    $this->assertEquals($user->amountRequested, self::YEARLY_PRICE);
                    $timesCharged++;
                }

                $totalAmountBilled += $user->amountRequested;
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check the user was charged the correct number of times
        $this->assertEquals(self::PART_YEARS_IN_SIMULATION, $timesCharged);

        // Check the number of charge logs matches the number of times we tried to bill them
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(self::PART_YEARS_IN_SIMULATION, $numberOfChargeLogs);

        // Check the total amount billed is what we think it should be (number of years * yearly price)
        $expectedTotal = self::PART_YEARS_IN_SIMULATION * self::YEARLY_PRICE;
        $this->assertEquals($expectedTotal, $totalAmountBilled);
    }

    /**
     * @test
     */
    public function yearlyBilling_oneUser_tenCompanies()
    {
        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION)->addDays(1);
        $billingDetails = $this->createBillingDetails(['period' => 'annually']);
        $user = $this->createUser(['name' => 'User #1', 'email' => 'paddy+user1@catalex.nz', 'billing_detail_id' => $billingDetails->id]);
        $user->services()->attach(Service::where('name', 'Good Companies')->first());

        $numberOfCompanies = 10;
        $fakeCompanies = $this->massGenerateCompanies($user, $numberOfCompanies);

        $totalAmountBilled = 0;
        $timesCharged = 0;

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $this->assertEquals($user->paymentLastRequested, Carbon::today());

                if ($user->amountRequested > 0) {
                    $this->assertEquals($user->amountRequested, self::YEARLY_PRICE * $numberOfCompanies);
                    $timesCharged++;
                }

                $totalAmountBilled += $user->amountRequested;
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check the user was charged the correct number of times
        $this->assertEquals(self::PART_YEARS_IN_SIMULATION, $timesCharged);

        // Check the number of charge logs matches the number of times we tried to bill them
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(self::PART_YEARS_IN_SIMULATION, $numberOfChargeLogs);

        // Check the total amount billed is what we think it should be (number of years * yearly price)
        $expectedTotal = self::PART_YEARS_IN_SIMULATION * self::YEARLY_PRICE * $numberOfCompanies;
        $this->assertEquals($expectedTotal, $totalAmountBilled);
    }

    /**
     * @test
     */
    public function yearlyBilling_oneUser_noCompanies()
    {
        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION)->addDays(1);
        $billingDetails = $this->createBillingDetails(['period' => 'annually']);
        $user = $this->createUser(['name' => 'User #1', 'email' => 'paddy+user1@catalex.nz', 'billing_detail_id' => $billingDetails->id]);
        $user->services()->attach(Service::where('name', 'Good Companies')->first());

        $numberOfCompanies = 0;
        $fakeCompanies = $this->massGenerateCompanies($user, $numberOfCompanies);

        $totalAmountBilled = 0;
        $timesCharged = 0;

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $this->assertEquals($user->paymentLastRequested, Carbon::today());

                if ($user->amountRequested > 0) {
                    $this->assertEquals($user->amountRequested, self::YEARLY_PRICE * $numberOfCompanies);
                    $timesCharged++;
                }

                $totalAmountBilled += $user->amountRequested;
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check the user was charged the correct number of times
        $this->assertEquals(0, $timesCharged);

        // Check the number of charge logs matches the number of times we tried to bill them
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(0, $numberOfChargeLogs);

        // Check the total amount billed is what we think it should be (number of years * yearly price)
        $expectedTotal = self::PART_YEARS_IN_SIMULATION * self::YEARLY_PRICE * $numberOfCompanies;
        $this->assertEquals($expectedTotal, $totalAmountBilled);
    }

    /**
     * @test
     */
    public function yearlyBilling_oneUser_billingDayIs31st()
    {
        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION)->addDays(1);
        $billingDetails = $this->createBillingDetails(['period' => 'annually', 'billing_day' => 31]);
        $user = $this->createUser(['name' => 'User #1', 'email' => 'paddy+user1@catalex.nz', 'billing_detail_id' => $billingDetails->id]);
        $user->services()->attach(Service::where('name', 'Good Companies')->first());

        $numberOfCompanies = 0;
        $fakeCompanies = $this->massGenerateCompanies($user, $numberOfCompanies);

        $totalAmountBilled = 0;
        $timesCharged = 0;

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $this->assertEquals($user->paymentLastRequested, Carbon::today());

                if ($user->amountRequested > 0) {
                    $this->assertEquals($user->amountRequested, self::YEARLY_PRICE * $numberOfCompanies);
                    $timesCharged++;
                }

                $totalAmountBilled += $user->amountRequested;
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check the user was charged the correct number of times
        $this->assertEquals(0, $timesCharged);

        // Check the number of charge logs matches the number of times we tried to bill them
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(0, $numberOfChargeLogs);

        // Check the total amount billed is what we think it should be (number of years * yearly price)
        $expectedTotal = self::PART_YEARS_IN_SIMULATION * self::YEARLY_PRICE * $numberOfCompanies;
        $this->assertEquals($expectedTotal, $totalAmountBilled);
    }

    /**
     * @test
     */
    public function monthlyBilling_oneUser_userIdFree_oneCompany()
    {
        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION)->addDays(1);
        $billingDetails = $this->createBillingDetails();
        $user = $this->createUser(['name' => 'User #1', 'email' => 'paddy+user1@catalex.nz', 'billing_detail_id' => $billingDetails->id, 'free' => true]);
        $user->services()->attach(Service::where('name', 'Good Companies')->first());

        $fakeCompanies = [
            ['userId' => $user->id, 'companyId' => 72773, 'active' => true, 'companyName' => 'Johnny Mate']
        ];

        $totalAmountBilled = 0;

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $this->assertEquals($user->paymentLastRequested, Carbon::today());
                $this->assertEquals($user->amountRequested, self::MONTHLY_PRICE);

                $totalAmountBilled += $user->amountRequested;
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check no charge logs were created
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(0, $numberOfChargeLogs);

        //heck nothing was charged
        $expectedTotal = 0;
        $this->assertEquals($expectedTotal, $totalAmountBilled);
    }

    /**
     * @test
     */
    public function monthlyBilling_oneUser_userIsFree_noCompanies()
    {
        $dayAfterSimulation = Carbon::now()->addMonths(self::MONTHS_IN_SIMULATION)->addDays(1);
        $billingDetails = $this->createBillingDetails();
        $user = $this->createUser(['name' => 'User #1', 'email' => 'paddy+user1@catalex.nz', 'billing_detail_id' => $billingDetails->id, 'free' => true]);
        $user->services()->attach(Service::where('name', 'Good Companies')->first());

        $fakeCompanies = [];

        $lastBilled = null;
        $totalAmountBilled = 0;

        while (Carbon::now()->lt($dayAfterSimulation)) {
            // Create the sync command object, hand it our fake companies, and run it
            $syncCommand = new SyncCommand();
            $syncCommand->fakeCompanies = $fakeCompanies;
            $syncCommand->handle();

            if ($user->shouldBill()) {
                $user->bill();

                $lastBilled = Carbon::today();

                $this->assertEquals($user->paymentLastRequested, $lastBilled);
                $this->assertEquals($user->amountRequested, 0);

                $totalAmountBilled += $user->amountRequested;
            }

            // Increment the day
            Carbon::setTestNow(Carbon::today()->addDays(1));
        }

        // Check no charge logs were created
        $numberOfChargeLogs = $user->chargeLogs()->count();
        $this->assertEquals(0, $numberOfChargeLogs);

        // Check the user was not charged
        $expectedTotal = 0;
        $this->assertEquals($expectedTotal, $totalAmountBilled);
    }
}
