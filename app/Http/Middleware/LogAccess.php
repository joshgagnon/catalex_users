<?php namespace App\Http\Middleware;

use Closure;
use App\AccessLog;
use Illuminate\Contracts\Auth\Guard;

class LogAccess {

	/**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * Create a new filter instance.
	 *
	 * @param  Guard  $auth
	 * @return void
	 */
	public function __construct(Guard $auth) {
		$this->auth = $auth;
	}

	/**
	 * Log details of the incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		$user = $this->auth->user();

		$userId = $user ? $user->id : null;

		$route = $request->path();

		// Don't log when checking logs, and wait for after login
		if(!in_array($route, ['admin/access-log', 'auth/login'])) {
			AccessLog::create(['user_id' => $userId, 'route' => $route]);
		}

		$response = $next($request);

		// Log successful logins
		$user = $this->auth->user();
		if($user && $route === 'auth/login') {
			AccessLog::create(['user_id' => $user->id, 'route' => $route]);
		}

		return $response;
	}
}
