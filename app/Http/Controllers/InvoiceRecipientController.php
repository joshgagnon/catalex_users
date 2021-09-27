<?php

namespace App\Http\Controllers;

use App\InvoiceRecipient;
use Illuminate\Http\Request;

class InvoiceRecipientController extends Controller
{
    public function __construct(Request $request)
    {
        $user = $request->user();
        if(!$user) {
            return;
        }
        if (!$user->organisation_id) {
           # abort(404);
        }

        if (!$user->hasRole('organisation_admin')) {
           # abort(404);
        }
    }

    public function index(Request $request)
    {
        $orgId = $request->user()->organisation_id;
        $recipients = InvoiceRecipient::where('organisation_id', $orgId)->get();

        return view('invoice-recipients.index')->with(['recipients' => $recipients]);
    }

    public function create()
    {
        return view('invoice-recipients.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, InvoiceRecipient::$validationRules);

        $requestInput = $request->all();
        $orgId = $request->user()->organisation_id;

        InvoiceRecipient::forceCreate([
            'name'            => $requestInput['name'],
            'email'           => $requestInput['email'],
            'organisation_id' => $orgId,
        ]);

        return redirect()->route('invoice-recipients.index')->with(['success' => 'Invoice recipient created.']);
    }

    public function edit($recipientId)
    {
        $recipient = InvoiceRecipient::find($recipientId);

        if (!$recipient) {
            abort(404);
        }

        return view('invoice-recipients.edit')->with(['recipient' => $recipient]);
    }

    public function update(Request $request, $recipientId)
    {
        $recipient = InvoiceRecipient::find($recipientId);

        if (!$recipient) {
            abort(404);
        }

        $this->validate($request, InvoiceRecipient::$validationRules);
        $recipient->update($request->all());

        return redirect()->route('invoice-recipients.index')->with(['success' => 'Invoice recipient updated.']);
    }

    public function delete($recipientId)
    {
        $recipient = InvoiceRecipient::find($recipientId);

        if (!$recipient) {
            abort(404);
        }

        $recipient->delete();
        return redirect()->route('invoice-recipients.index')->with(['success' => 'Invoice recipient deleted.']);
    }
}
