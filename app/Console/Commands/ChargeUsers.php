<?php namespace App\Console\Commands;

use App\User;
use App\Organisation;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ChargeUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:charge-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attempt to rebill anyone with due subscriptions or outstanding pro-rata members.';

    /**
     * Attempt to rebill anyone with due subscriptions or outstanding pro-rata members.
     *
     * @return mixed
     */
    public function handle()
    {
        // Bill all users without an organisation
        foreach (User::all() as $user) {
            if ($user->shouldBill()) {
                $user->bill();
            }
        }

        // Bill all organisations
        foreach (Organisation::all() as $organisation) {
            if ($organisation->shouldBill()) {
                $organisation->bill();
            }
        }
    }
}
