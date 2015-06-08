<?php namespace App\Http\Middleware;

use Closure;

class TrimInput {

	/**
	 * Trim leading and trailing spaces from GET/POST params globally.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		$request->merge(array_map('trim', $request->all()));

		return $next($request);
	}
}
