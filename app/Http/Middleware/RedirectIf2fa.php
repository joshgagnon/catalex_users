<?php

namespace App\Http\Middleware;

use Closure;
use PragmaRX\Google2FALaravel\Support\Authenticator;

class RedirectIf2fa
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        $authenticator = app(Authenticator::class)->boot($request);
        $is2faAuthenticated = $authenticator->isAuthenticated();
        if ($user && $request->user()->google2fa_secret && !$is2faAuthenticated) {
            if($request->route()->getName() !== 'otp') {
               $request->session()->set('next', $request->fullUrl());
            }
            return $authenticator->makeRequestOneTimePasswordResponse();
        }
        return $next($request);
    }
}