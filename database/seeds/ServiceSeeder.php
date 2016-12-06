<?php

use Illuminate\Database\Seeder;
use App\Service;

class ServiceSeeder extends Seeder
{
    /**
     * Add all services to the services table
     *
     * @return void
     */
    public function run()
    {
        Service::create(['name' => 'Law Browser']);
        Service::create(['name' => 'Good Companies', 'is_paid_service' => true]);
    }
}
