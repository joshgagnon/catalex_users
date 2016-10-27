<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AddOAuthClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oauth:add-client {--client_id=} {--secret=} {--name=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add an OAuth client';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $clientId = $this->option('client_id');

        if (strlen($clientId) < 1 || strlen($clientId) > 40) {
            $this->error('client_id between 1 and 40 (inclusive)');
        } else {
            $secret = $this->option('secret');
            $name = $this->option('name');

            \DB::table('oauth_clients')->insert([
                'id' => $clientId,
                'secret' => $secret,
                'name' => $name,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);

            $this->info('Added ' . $name . ' | ' .'client_id: ' . $clientId . ', secret: ' . $secret);
        }
    }
}
