<?php

use App\BillingDetail;
use App\BillingItem;
use App\CardDetail;
use Tests\Stub\Organisation;
use App\Role;
use Carbon\Carbon;
use Tests\Stub\User;

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    protected static $migrationsRun = false;
    protected $baseUrl = 'http://localhost';

    private $massCreateBillingItemIdCounter = 1;
    private $userCounter = 1;

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

    protected function createUser($overrides = [], $serviceIds = [])
    {
        $defaults = [
            'name'              => 'User',
            'email'             => 'user' . $this->userCounter++ . '@example.com',
            'password'          => bcrypt('password'),
            'active'            => true,
            'billing_detail_id' => null,
        ];

        $userData = array_merge($defaults, $overrides);

        $user = User::forceCreate($userData);

        // Add the registered user role
        $registeredRole = Role::where('name', 'registered_user')->first();
        $user->addRole($registeredRole);

        // Add the services
        $user->syncSubscriptions($serviceIds);

        return $user;
    }

    protected function createBillingDetails($overrides = [])
    {
        $defaults = [
            'period'      => 'monthly',
            'billing_day' => 1,
        ];

        $billingData = array_merge($defaults, $overrides);

        return BillingDetail::create($billingData);
    }

    protected function createCardDetails($billingDetailId=null, $overrides = [], $billingDetailOverrides = null)
    {
        if (!$billingDetailId) {
            $billingDetailId = $this->createBillingDetails($billingDetailOverrides)->id;
        }

        $defaults = [
            'card_token' => '1234123412341234',
            'expiry_date' => '0622',
            'masked_card_number' => '1234xxxxxx1234',
        ];

        $cardData = array_merge($defaults, $overrides);

        $cardDetails = CardDetail::create($cardData);

        BillingDetail::find($billingDetailId)->update(['card_detail_id' => $cardDetails->id]);

        return $cardDetails;
    }

    protected function createUserWithBilling($userOverrides = [], $billingOverrides = [], $serviceIds = [])
    {
        $billingDetail = $this->createBillingDetails($billingOverrides);

        $userData = array_merge(['billing_detail_id' => $billingDetail->id], $userOverrides);

        $user = $this->createUser($userData, $serviceIds);

        return $user;
    }

    protected function createOrganisation($overrides = [], $orgAdmin=null)
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

    protected function massCreateBillingItems($userId, $serviceId, $numberOfItems)
    {
        $billingItems = [];

        for ($index = 0; $index < $numberOfItems; $index++) {
            $billingItems[] = [
                'user_id'    => $userId,
                'service_id' => $serviceId,
                'item_id'    => 'item_id_' . $this->massCreateBillingItemIdCounter++,
                'item_type'  => 'gc_company',
                'json_data'  => '{\"company_name\": \"test\"}',
                'active'     => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        return BillingItem::insert($billingItems);
    }
}

