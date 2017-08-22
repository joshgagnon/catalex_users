<?php

namespace Tests\Stub;

use App\ChargeLog;
use Carbon\Carbon;

class User extends \App\User
{
    public $paymentLastRequested;
    public $amountLastRequested;

    public $totalEverRequested = 0;
    public $timesBilled = 0;

    protected function requestPayment($totalDollarsDue)
    {
        $this->paymentLastRequested = Carbon::today();
        $this->amountLastRequested = $totalDollarsDue;

        $this->totalEverRequested += $totalDollarsDue;

        $this->timesBilled++;

        return true;
    }

    /**
     * @param \App\ChargeLog $chargeLog
     */
    protected function sendInvoices(ChargeLog $chargeLog) { } // do nothing
}
