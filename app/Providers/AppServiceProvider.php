<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}

	/**
	 * Register any application services.
	 *
	 * This service provider is a great spot to register your various container
	 * bindings with the application. As you can see, we are registering our
	 * "Registrar" implementation here. You can add your own bindings too!
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind(
			'Illuminate\Contracts\Auth\Registrar',
			'App\Services\Registrar'
		);

		$this->app->bind('Illuminate\Contracts\Auth\UserProvider', function() {
			return new \Illuminate\Auth\EloquentUserProvider($this->app->make('Illuminate\Contracts\Hashing\Hasher'), $this->app['config']['auth.model']);
		});

		$this->app->bind('App\Services\ResetBroker', function() {
			return new \App\Services\ResetBroker(
				$this->app->make('Illuminate\Auth\Passwords\TokenRepositoryInterface'),
				$this->app->make('Illuminate\Contracts\Auth\UserProvider'),
				$this->app->make('Illuminate\Contracts\Mail\Mailer'),
				'emails.reset-password'
			);
		});

	}

}
