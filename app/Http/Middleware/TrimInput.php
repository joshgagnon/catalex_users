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
		$recursiveTrim = function($input) use (&$recursiveTrim) {
			if(is_array($input)) return array_map($recursiveTrim, $input);
			return trim($input);
		};

		$request->merge(array_map($recursiveTrim, $request->all()));

		return $next($request);
	}
}
