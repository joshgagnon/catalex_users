<?php

namespace Tests\Stub;

use App\ChargeLog;

class Organisation extends \App\Organisation
{
    /**
     * @param \App\ChargeLog $chargeLog
     */
    protected function sendInvoices(ChargeLog $chargeLog) { } // do nothing
}
