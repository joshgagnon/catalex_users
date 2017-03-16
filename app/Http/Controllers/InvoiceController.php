<?php

namespace App\Http\Controllers;

use App\ChargeLog;
use App\Library\StringManipulation;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class InvoiceController extends Controller
{
    public function render(ChargeLog $chargeLog)
    {
        return $chargeLog->renderInvoice();
    }

    public function resend(Request $request, ChargeLog $chargeLog)
    {
        if (!$request->user()->hasRole('global_admin')) {
            abort(403, 'Forbidden');
        }

        $users = $chargeLog->sendInvoices();

        return redirect()->back()->withSuccess('Invoice ' . $chargeLog->timestamp->format('j M Y') . ' queued to be resent to ' . sizeof($users) . ' user(s)');
    }
}
