<?php namespace App\Http\Middleware;

use View;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Session;

class UserView {

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
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
	    $user = null;
        $showBilling = false;
        $showSubscriptions = false;
        $isImpersonating = false;

		if ($this->auth->check()) {
		    $user = $this->auth->user();

            $showBilling = !$user->organisation_id || $user->can('edit_own_organisation');
            $showSubscriptions = !$user->organisation || $user->can('edit_own_organisation');
            $isImpersonating = Session::has('admin_id');
		}

        View::share([
            'user' => $user,
            'showBilling' => $showBilling,
            'showSubscriptions' => $showSubscriptions,
            'isImpersonating' => $isImpersonating,
        ]);

		return $next($request);
	}
}
