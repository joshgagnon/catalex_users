<?php

use App\Library\Billing;

class LibBillingTest extends TestCase
{
    protected $runMigrations = false;

    /**
     * @test
     */
    public function includingGst()
    {
        $excludingGst = 10;
        $includingGst = $excludingGst * 1.15;

        $actualGst = Billing::includingGST($includingGst);
        $expectedGst = Billing::formatDollars($includingGst - $excludingGst);

        $this->assertEquals($actualGst, $expectedGst);
    }

    /**
     * @test
     */
    public function includingGst_bigNumbers()
    {
        $excludingGst = 985674393278;
        $includingGst = $excludingGst * 1.15;
        
        $actualGst = Billing::includingGST($includingGst);
        $expectedGst = Billing::formatDollars($includingGst - $excludingGst);

        $this->assertEquals($actualGst, $expectedGst);
    }

    /**
     * @test
     */
    public function includingGst_zero()
    {
        $excludingGst = 0;
        $includingGst = $excludingGst * 1.15;
        
        $actualGst = Billing::includingGST($includingGst);
        $expectedGst = Billing::formatDollars($includingGst - $excludingGst);

        $this->assertEquals($actualGst, $expectedGst);
    }

    /**
     * @test
     */
    public function centsToDollars()
    {
        $cents = 100;

        $actual = Billing::centsToDollars($cents);
        $expected = Billing::formatDollars(1);

        $this->assertEquals($actual, $expected);
    }

    /**
     * @test
     */
    public function centsToDollars_bigNumbers()
    {
        $cents = 87543487289;

        $actual = Billing::centsToDollars($cents);
        $expected = Billing::formatDollars(875434872.89);

        $this->assertEquals($actual, $expected);
    }

    /**
     * @test
     */
    public function centsToDollars_zero()
    {
        $cents = 0;

        $actual = Billing::centsToDollars($cents);
        $expected = Billing::formatDollars(0);

        $this->assertEquals($actual, $expected);
    }

    /**
     * @test
     */
    public function formatDollars()
    {
        
    }
}
