<?php

use App\Library\Billing;

class LibBillingTest extends TestCase
{
    protected $runMigrations = false;
    
    /**
     * includingGst
     */

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
     * applyDiscount
     */
    
    /**
     * @test
     */
    public function applyDiscount()
    {
        $discountPercent = '10';
        $beforeDiscount = '10.00';
        $expectedAfterDiscount = '9.00';
        
        $actualAfterDiscount = Billing::applyDiscount($beforeDiscount, $discountPercent);
        
        $this->assertEquals($expectedAfterDiscount, $actualAfterDiscount);
    }
    
    /**
     * @test
     */
    public function applyDiscount_bigNumbers()
    {
        $discountPercent = '12.521';
        $beforeDiscount = '6548645148.45';
        $expectedAfterDiscount = '5728689289.41';
        
        $actualAfterDiscount = Billing::applyDiscount($beforeDiscount, $discountPercent);
        
        $this->assertEquals($expectedAfterDiscount, $actualAfterDiscount);
    }
    
    /**
     * @test
     */
    public function applyDiscount_zero()
    {
        $discountPercent = '22.29';
        $beforeDiscount = '0';
        $expectedAfterDiscount = '0';
        
        $actualAfterDiscount = Billing::applyDiscount($beforeDiscount, $discountPercent);
        
        $this->assertEquals($expectedAfterDiscount, $actualAfterDiscount);
    }
    
    /**
     * centsToDollars
     */

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
     * formatDollars
     */

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
