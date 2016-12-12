<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;
use App\BillingDetail;
use App\User;

class BillableTrait_isBillingDayMethodTest extends TestCase
{
    use DatabaseTransactions;

    private function createBillingDetails($overrides=[])
    {
        $defaults = [
            'period' => 'annually',
            'billing_day' => 1,
        ];

        $billingData = array_merge($defaults, $overrides);

        return BillingDetail::create($billingData);
    }

    private function createUser($overrides=[])
    {
        $defaults = [
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'active' => true,
            'billing_detail_id' => null,
        ];

        $userData = array_merge($defaults, $overrides);

        return User::create($userData);
    }

    /**
     * @test
     */
    public function isBillingDay()
    {
        $billingDetails = $this->createBillingDetails(['period' => 'monthly');
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
        $billingDetails = $this->createBillingDetails('monthly',  Carbon::parse('-1 day')->day);
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
        $billingDetails = $this->createBillingDetails('monthly',  31);
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
        $billingDetails = $this->createBillingDetails('monthly',  31);
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
        $billingDetails = $this->createBillingDetails(null, 31);
        $user = $this->createUser(['billing_detail_id' => $billingDetails->id]);

        $actual = $user->isBillingDay(Carbon::parse('last day of February 2015'));
        $expected = true;

        $this->assertEquals($expected, $actual);
    }
}
