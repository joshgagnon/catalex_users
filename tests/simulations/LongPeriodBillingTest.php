<?php

use Carbon\Carbon;

class LongPeriodBillingTest extends TestCase
{
    protected $seeder = 'ServiceSeeder';

    public function testExample()
    {
        $lastDayOfSimulation = Carbon::now()->addMonths(25);
        
        // Create initial state

        while (Carbon::now()->lt($lastDayOfSimulation)) {
            
        }

        // Do initial import with stubbed out GC Sync command

        // First checks

        // Move forward in time

        // Set carbon time back to normal
        Carbon::setTestNow(Carbon::today()->addDays(1));
    }
}
