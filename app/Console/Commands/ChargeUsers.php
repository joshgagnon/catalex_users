<?php namespace App\Console\Commands;

use App\User;
use App\Organisation;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ChargeUsers extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'billing:charge-all';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Attempt to rebill anyone with due subscriptions or outstanding pro-rata members.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Attempt to rebill anyone with due subscriptions or outstanding pro-rata members.
	 *
	 * @return mixed
	 */
	public function fire() {
		foreach(User::all() as $user) {
			$user->rebill();
		}

		foreach(Organisation::all() as $organisation) {
			$organisation->billProrataMembers();
			$organisation->rebill();
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments() {
		return [];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions() {
		return [];
	}
}
