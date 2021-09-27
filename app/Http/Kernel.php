<?php namespace App\Http;

use App\Http\Middleware\RedirectIf2fa;
use App\Http\Middleware\RedirectShadowUser;
use App\Http\Middleware\IsShadowUser;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {

	/**
	 * The application's global HTTP middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = [
		'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
		'Illuminate\Cookie\Middleware\EncryptCookies',
		'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
		'Illuminate\Session\Middleware\StartSession',
		'Illuminate\View\Middleware\ShareErrorsFromSession',
		'App\Http\Middleware\LogAccess',
		'App\Http\Middleware\UserView',
		//'App\Http\Middleware\VerifyCsrfToken',
		'App\Http\Middleware\TrimInput',
        \LucaDegasperi\OAuth2Server\Middleware\OAuthExceptionHandlerMiddleware::class
	];

	/**
	 * The application's route middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = [
	    'web' => 'App\Http\Middleware\Web',
        'auth' => 'App\Http\Middleware\Authenticate',
        'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
        'guest' => 'App\Http\Middleware\RedirectIfAuthenticated',
        'admin' => 'App\Http\Middleware\CheckGlobalAdmin',
        'csrf' => 'App\Http\Middleware\VerifyCsrfToken',
        'redirect-shadow-users' => RedirectShadowUser::class,
        'redirect-if-2fa' => RedirectIf2fa::class,
        'is-shadow-user' => IsShadowUser::class,
        'oauth' => \LucaDegasperi\OAuth2Server\Middleware\OAuthMiddleware::class,
        'oauth-user' => \LucaDegasperi\OAuth2Server\Middleware\OAuthUserOwnerMiddleware::class,
        'oauth-client' => \LucaDegasperi\OAuth2Server\Middleware\OAuthClientOwnerMiddleware::class,
        'check-authorization-params' => \LucaDegasperi\OAuth2Server\Middleware\CheckAuthCodeRequestMiddleware::class,
	];

}
