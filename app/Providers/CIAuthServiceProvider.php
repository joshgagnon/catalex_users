<?php namespace App\Providers;

use App\User;
use Illuminate\Support\ServiceProvider;
use App\Auth\CaseInsensitiveUserProvider;

class CIAuthServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot() {
		$this->app['auth']->extend('caseinsensitive', function() {
			return new CaseInsensitiveUserProvider($this->app['hash'], $this->app['config']['auth.model']);
		});
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register() {

	}
}
