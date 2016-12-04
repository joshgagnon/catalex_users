<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AddOAuthClientEndpoint extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oauth:add-endpoint {--client_id=} {--endpoint=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a legal endpoint to an OAuth client';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $clientId = $this->option('client_id');
        $client = \DB::table('oauth_clients')->find($clientId);

        if ($client) {
            $endpoint = $this->option('endpoint');

            \DB::table('oauth_client_endpoints')->insert([
                'client_id' => $clientId,
                'redirect_uri' => $endpoint,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);

            $this->info('Added endpoint: ' . $endpoint . ' to ' . $client->name);
        } else {
            $this->error('Client doesn\'t exist with client_id: ' . $clientId);
        }
    }
}
