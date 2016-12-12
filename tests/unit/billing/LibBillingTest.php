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

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function centsToDollars_bigNumbers()
    {
        $cents = 87543487289;

        $actual = Billing::centsToDollars($cents);
        $expected = '875434872.89';

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function centsToDollars_zero()
    {
        $cents = 0;

        $actual = Billing::centsToDollars($cents);
        $expected = Billing::formatDollars(0);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function formatDollars()
    {
        $dollars = 1;

        $actual = Billing::formatDollars($dollars);
        $expected = '1.00';

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function formatDollars_roundsUpCorrectly()
    {
        $dollars = 99.995;

        $actual = Billing::formatDollars($dollars);
        $expected = '100.00';

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function formatDollars_roundsDownCorrectly()
    {
        $dollars = 99.993;

        $actual = Billing::formatDollars($dollars);
        $expected = '99.99';

        $this->assertEquals($expected, $actual);
    }
}
