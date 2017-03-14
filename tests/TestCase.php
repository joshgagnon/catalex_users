<?php

use App\BillingDetail;
use Tests\Stub\User;
use App\Organisation;

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    protected $baseUrl = 'http://localhost';

    protected $runMigrations = true;
    protected $seeder = 'TestSeeder';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        if ($this->runMigrations) {
            $mirgrationOptions = !empty($this->seeder) ? ['--seeder' => $this->seeder] : [];
            $app['Illuminate\Contracts\Console\Kernel']->call('migrate:refresh', $mirgrationOptions);
        }

        return $app;
    }

    protected function createUser($overrides=[])
    {
        $defaults = [
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'active' => true,
            'billing_detail_id' => null,
        ];

        $userData = array_merge($defaults, $overrides);

        return User::create($userData);
    }

    protected function createBillingDetails($overrides=[])
    {
        $defaults = [
            'period' => 'monthly',
            'billing_day' => 1,
        ];

        $billingData = array_merge($defaults, $overrides);

        return BillingDetail::create($billingData);
    }

    protected function createOrganisation($overrides=[], $orgAdmin=null)
    {
        $defaults = ['name' => 'Test Org'];
        $orgData = array_merge($defaults, $overrides);
        
        $organisation = Organisation::create($orgData);

        if (!$orgAdmin) {
            $orgAdmin = $this->createUser();
        }

        $orgAdmin->organisation_id = $organisation->id;
        $orgAdmin->save();

        $orgAdmin->addRole('organisation_admin');

        return $organisation;
    }
}

