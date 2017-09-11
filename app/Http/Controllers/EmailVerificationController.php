<?php

namespace App\Http\Controllers;

use App\EmailVerificationToken;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, $token)
    {
        $user = $request->user();
        $tokenMatchesUser = EmailVerificationToken::where('token', $token)->where('user_id', $user->id)->exists();

        if ($tokenMatchesUser) {
            $user->email_verified = true;
            $user->save();

            return redirect()->to('index')->with(['success' => 'Email verified.']);
        }
        else {
            return redirect()->to('index')->with(['error' => 'Failed to verify email: token did not match email.']);
        }
    }
}
