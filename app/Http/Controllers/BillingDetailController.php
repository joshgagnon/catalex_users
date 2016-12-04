<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class BillingDetailController extends Controller
{
    public function create()
    {
        $user = Auth::user();

        if ($user->hasBillingDetail()) {
            return redirect()->back()->withErrors(['You or your organisation already have billing setup']);
        }
    }

    public function store()
    {
        $user = Auth::user();
        
        if ($user->hasBillingDetail()) {
            return redirect()->back()->withErrors(['You or your organisation already have billing setup']);
        }
        
        
    }

    public function edit()
    {
        
    }
}
