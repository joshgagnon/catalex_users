<?php

namespace App\Http\Controllers;

use App\ChargeLog;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class InvoiceController extends Controller
{
    public function render(ChargeLog $invoice)
    {
        return $invoice->renderInvoice();
    }
}
