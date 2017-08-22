<?php

namespace Tests\Stub;

use App\ChargeLog;
use Carbon\Carbon;

class User extends \App\User
{
    public $paymentLastRequested;
    public $amountRequested;

    protected function requestPayment($totalDollarsDue)
    {
        $this->paymentLastRequested = Carbon::today();
        $this->amountRequested = $totalDollarsDue;

        return true;
    }

    /**
     * @param \App\ChargeLog $chargeLog
     */
    protected function sendInvoices(ChargeLog $chargeLog) { } // do nothing
}
