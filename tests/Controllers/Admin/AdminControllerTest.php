<?php

use App\Library\Billing;
use App\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function admin_can_create_user()
    {
        $adminUser = $this->createUser();
        $adminUser->addRole(Role::where('name', '=', 'global_admin')->first());

        Auth::loginUsingId($adminUser->id);

        $this->visit('/admin/add-user')
            ->type('Johnny', 'name')
            ->type('johnny@example.com', 'email')
            ->type('110 Street', 'address_line_1')
            ->type('Somewhere', 'address_line_2')
            ->type('Dunedin', 'city')
            ->type('New Zealand', 'state')
            ->press('Create User');

        $this->seePageIs('/admin/users')
            ->see('User Johnny successfully created.');
    }

    /**
     * @test
     */
    public function admin_can_edit_organisation()
    {
        $adminUser = $this->createUser();
        $adminUser->addRole(Role::where('name', '=', 'global_admin')->first());

        $originalName = 'Pre edit name';
        $editedName = 'Post edit name';

        $orgAdmin = $this->createUser(['email' => 'orgadmin@me.com']);
        $org = $this->createOrganisation(['name' => $originalName], $orgAdmin);

        Auth::loginUsingId($adminUser->id);

        $this->visit(url('/admin/edit-organisation', $org->id))
            ->see($originalName)
            ->type($editedName, 'name')
            ->press('Update')
            ->see('Organisation "' . $editedName . '" successfully updated.');

        $org = $org->fresh();

        $this->assertEquals($editedName, $org->name);
    }

    /**
     * @test
     */
    public function becoming_invoice_customer_creates_billing_details()
    {
        $admin = $this->createUser();
        $admin->addRole(Role::where('name', '=', 'global_admin')->first());
        $org = $this->createOrganisation();

        Auth::loginUsingId($admin->id);

        $this->visit('/admin/edit-organisation/' . $org->id)
            ->dontSeeIsChecked('is_invoice_customer')
            ->check('is_invoice_customer')
            ->press('Update');

        $org = $org->fresh();
        $billingDetails = $org->billing_detail()->first();

        $this->assertNotNull($billingDetails);

        $expectedBillingDay = Carbon::now()->addDays(Billing::DAYS_IN_TRIAL_PERIOD)->day;
        $this->assertEquals($expectedBillingDay, $billingDetails->billing_day);
    }
}
