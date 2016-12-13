<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;
use App\BillingDetail;
use App\User;

class BillableTrait_isBillingDayMethodTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function isBillingDay()
    {
        $billingDetails = $this->createBillingDetails(['billing_day' => Carbon::today()->day]);
        $user = $this->createUser(['billing_detail_id' => $billingDetails->id]);

        $actual = $user->isBillingDay(Carbon::now());
        $expected = true;

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function isBillingDay_notBillingDay()
    {
        $billingDetails = $this->createBillingDetails(['billing_day' => Carbon::parse('-1 day')->day]);
        $user = $this->createUser(['billing_detail_id' => $billingDetails->id]);

        $actual = $user->isBillingDay(Carbon::now());
        $expected = false;

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function isBillingDay_lastDayOfMonth()
    {
        $billingDetails = $this->createBillingDetails(['billing_day' => 31]);
        $user = $this->createUser(['billing_detail_id' => $billingDetails->id]);

        $actual = $user->isBillingDay(Carbon::parse('last day of July'));
        $expected = true;

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function isBillingDay_lastDayOfMonth_isBeforeBillingDay()
    {
        $billingDetails = $this->createBillingDetails(['billing_day' => 31]);
        $user = $this->createUser(['billing_detail_id' => $billingDetails->id]);

        $actual = $user->isBillingDay(Carbon::parse('last day of September'));
        $expected = true;

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function isBillingDay_lastDayOfMonth_isBeforeBillingDay_28DayMonth()
    {
        $billingDetails = $this->createBillingDetails(['period' => 'annually', 'billing_day' => 31]);
        $user = $this->createUser(['billing_detail_id' => $billingDetails->id]);

        $actual = $user->isBillingDay(Carbon::parse('last day of February 2015'));
        $expected = true;

        $this->assertEquals($expected, $actual);
    }
}
