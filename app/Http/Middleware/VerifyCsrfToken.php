<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		try {
			return parent::handle($request, $next);
		}
		catch(\Illuminate\Session\TokenMismatchException $e) {
			return redirect()->back()->withErrors(['Session has expired, please try again.']);
		}
	}

}
