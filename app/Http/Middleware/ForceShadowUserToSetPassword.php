<?php

namespace App\Http\Middleware;

use Closure;

class ForceShadowUserToSetPassword
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

        if (!$user || !$user->is_shadow_user) {
            return $next($request);
        }

        return redirect()->route('shadow-user.promote', ['next' => $request->getRequestUri()]);
    }
}
