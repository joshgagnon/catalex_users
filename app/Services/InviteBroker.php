<?php namespace App\Services;

use File;
use Closure;
use Illuminate\Auth\Passwords\PasswordBroker;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class InviteBroker extends PasswordBroker {

	public function emailResetLink(CanResetPasswordContract $user, $token, Closure $callback = null) {
		$html = view('emails.invite', compact('token', 'user'))->render();
		$css = File::get(public_path('/css/email.css'));

		$inliner = new CssToInlineStyles($html, $css);
		$markup = $inliner->convert();

		return $this->mailer->send('emails.echo', ['html' => $markup], function($m) use ($user, $token, $callback) {
			$m->to($user->getEmailForPasswordReset())->subject('You have been invited to use CataLex Law Browser');

			if(!is_null($callback)) call_user_func($callback, $m, $user, $token);
		});
	}
}
