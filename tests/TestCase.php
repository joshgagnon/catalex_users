<?php

use App\BillingDetail;
use App\Role;
use Tests\Stub\User;
use App\Organisation;

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    protected static $migrationsRun = false;
    protected $baseUrl = 'http://localhost';

    // Laravel boilerplate to create app for tests
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    // Run the migrations on setup, but only once
    public function setUp()
    {
        parent::setUp();

        if (!static::$migrationsRun) {
            Artisan::call('migrate:refresh', ['--seed' => true]);
            static::$migrationsRun = true;
        }
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

        $user = User::create($userData);

        // Add the registered user role
        $registeredRole = Role::where('name', 'registered_user')->first();
        $user->addRole($registeredRole);

        return $user;
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

    protected function createUserWithBilling($userOverrides=[], $billingOverrides=[])
    {
        $billingDetail = $this->createBillingDetails($billingOverrides);

        $userData = array_merge(['billing_detail_id' => $billingDetail->id], $userOverrides);

        $user = $this->createUser($userData);

        return $user;
    }

    protected function createOrganisation($overrides=[], $orgAdmin)
    {
        $defaults = ['name' => 'Test Org'];
        $orgData = array_merge($defaults, $overrides);
        
        $organisation = Organisation::create($orgData);

        $orgAdmin->organisation_id = $organisation->id;
        $orgAdmin->save();

        $orgAdmin->addRole('organisation_admin');

        return $organisation;
    }
}

