<?php

namespace Tests\Stub;

use Carbon\Carbon;

class User extends \App\User
{
    public $paymentLastRequested;
    public $amountRequested;

    public function sendInvoices($invoiceNumber, $listItems, $totalAmount, $gst, $orgName=null, $orgId=null)
    {
        // Do nothing
    }

    protected function requestPayment($totalDollarsDue)
    {
        $this->paymentLastRequested = Carbon::today();
        $this->amountRequested = $totalDollarsDue;

        return true;
    }
}
