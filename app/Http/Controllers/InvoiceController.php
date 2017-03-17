<?php

namespace App\Http\Controllers;

use App\ChargeLog;
use App\User;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function render(Request $request, ChargeLog $chargeLog)
    {
        if (!$this->canViewInvoice($chargeLog, $request->user())) {
            abort(403, 'Forbidden');
        }

        if (!$chargeLog->success || $chargeLog->pending) {
            abort(400, 'Invoices can only be generated for successful charges. This charge is either pending or has failed.');
        }

        return $chargeLog->renderInvoice();
    }

    public function download(Request $request, ChargeLog $chargeLog)
    {
        if (!$this->canViewInvoice($chargeLog, $request->user())) {
            abort(403, 'Forbidden');
        }

        if (!$chargeLog->success || $chargeLog->pending) {
            abort(400, 'Invoices can only be generated for successful charges. This charge is either pending or has failed.');
        }

        $invoicePath = $chargeLog->generateInvoice();
        return response()->download($invoicePath, 'Invoice.pdf', ['Content-Type: application/pdf']);
    }

    public function resend(Request $request, ChargeLog $chargeLog)
    {
        if (!$request->user()->hasRole('global_admin')) {
            abort(403, 'Forbidden');
        }

        if (!$chargeLog->success || $chargeLog->pending) {
            abort(400, 'Invoices can only be generated for successful charges. This charge is either pending or has failed.');
        }

        $users = $chargeLog->sendInvoices();

        return redirect()->back()->withSuccess('Invoice ' . $chargeLog->timestamp->format('j M Y') . ' queued to be resent to ' . sizeof($users) . ' user(s)');

    }

    private function canViewInvoice(ChargeLog $chargeLog, User $user)
    {
        if ($user->hasRole('global_admin')) {
            return true;
        }

        // Is this user's charge log
        if ($chargeLog->user_id) {
            return $chargeLog->user_id === $user->id;
        }

        // Is an organisation's charge log and user is allowed to edit their org
        if ($chargeLog->organisation_id === $user->organisation_id) {
            return $user->can('edit_own_organisation');
        }

        return false;
    }
}
