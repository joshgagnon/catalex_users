<?php

namespace App\Http\Controllers;

use App\ChargeLog;

class ChargeLogController extends Controller
{
    public function markAsSuccessful(ChargeLog $chargeLog)
    {
        $chargeLog->update([
            'success' => true,
            'pending' => false,
        ]);

        return redirect()->back()->with(['success' => 'Charge log marked as successful.']);
    }

    public function markAsFailed(ChargeLog $chargeLog)
    {
        $chargeLog->update([
            'success' => false,
            'pending' => false,
        ]);

        return redirect()->back()->with(['success' => 'Charge log marked as failed.']);
    }

    public function markAsPending(ChargeLog $chargeLog)
    {
        $chargeLog->update([
            'success' => false,
            'pending' => true,
        ]);

        return redirect()->back()->with(['success' => 'Charge log marked as pending.']);
    }
}
