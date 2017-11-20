<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ShadowUserController extends Controller
{
    public function promote(Request $request)
    {
        return view('auth.promote-shadow-user', [
            'next' => $request->query('next'),
        ]);
    }

    public function setPassword(Request $request)
    {
        $this->validate($request, ['password' => 'required|confirmed']);

        $user = $request->user();

        $user->is_shadow_user = false;
        $user->password = Hash::make($request->password);

        $user->save();

        $user->firstLoginToken()->delete();

        return redirect()->route('index');
    }
}
