<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		//'App\Console\Commands\SendExpiredTrialEmails',
		'App\Console\Commands\ChargeUsers',
		'App\Console\Commands\AddOAuthClient',
		'App\Console\Commands\AddOAuthClientEndpoint',
		'App\Console\Commands\SyncCompaniesFromGoodCompanies',
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule) {
		$schedule->exec('php artisan gc:sync-companies && php artisan billing:charge-all')->dailyAt('22:00');
	}
}
