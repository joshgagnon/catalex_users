<?php

namespace App\Http\Controllers;

use App\EmailVerificationToken;
use App\Library\Mail\EmailVerification;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function sendEmail(Request $request)
    {
        $user = $request->user();

        $tokenInstance = EmailVerificationToken::createToken($user);

        $email = new EmailVerification($user->name, $user->email, $tokenInstance->token);
        $email->send();

        return redirect()->route('index')->with(['success' => 'Email verification sent, please check your emails.']);
    }

    public function verify(Request $request, $token)
    {
        $user = $request->user();
        $tokenMatchesUser = EmailVerificationToken::where('token', $token)->where('user_id', $user->id)->exists();

        if ($tokenMatchesUser) {
            $user->email_verified = true;
            $user->save();

            return redirect()->route('index')->with(['success' => 'Email verified.']);
        }
        else {
            return redirect()->route('index')->with(['error' => 'Failed to verify email: token did not match email.']);
        }
    }
}
