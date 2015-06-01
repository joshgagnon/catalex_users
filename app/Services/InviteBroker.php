<?php namespace App\Services;

use Auth;
use Closure;
use App\Library\Mail;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class InviteBroker extends PasswordBroker {

	public function emailResetLink(CanResetPasswordContract $user, $token, Closure $callback = null) {
		return Mail::sendStyledMail('emails.invite', compact('token', 'user') + ['inviter' => Auth::user()->fullName()], $user->getEmailForPasswordReset(), $user->fullName(), 'Welcome to CataLex');
	}
}
