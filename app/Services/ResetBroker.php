<?php namespace App\Services;

use Closure;
use App\Library\Mail;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class ResetBroker extends PasswordBroker {

	public function emailResetLink(CanResetPasswordContract $user, $token, Closure $callback = null) {
		return Mail::sendStyledMail('emails.reset-password', compact('token', 'user'), $user->getEmailForPasswordReset(), $user->fullName(), 'CataLex Password Reset');
	}
}
