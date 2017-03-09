<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

Use App\Role;

class AdminControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function adminCreateUser()
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
}
