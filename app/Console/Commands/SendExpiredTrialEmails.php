<?php namespace App\Console\Commands;

use Config;
use App\User;
use Carbon\Carbon;
use App\Library\Mail;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SendExpiredTrialEmails extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'emails:trial-expiry';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send email to users with expired trial period.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Send emails to users with exired trials if they have not received a trial email before.
	 *
	 * @return mixed
	 */
	public function fire() {
		$lastTrial = Carbon::now()->subMinutes(Config::get('constants.trial_length_minutes'));

		// Get a list of users whose trial has expired and hasn't yet been sent an email
		$users = User::where('trial_expired_email_sent', '=', false)->where('created_at', '<', $lastTrial)->get();

		foreach($users as $user) {
			// Don't send emails to users who have paid already
			if($user->everBilled() || $user->billingExempt()) {
				continue;
			}

			// Don't send emails to organisation members who aren't admins (and therefore can't pay)
			if($user->organisation && !$user->can('edit_own_organisation')) {
				continue;
			}

			Mail::queueStyledMail('emails.trial-expired', ['user' => $user], $user->email, $user->fullName(), 'CataLex | Trial Period Over');
		}

		foreach($users as $user) {
			$user->trial_expired_email_sent = true;
			$user->save();
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
