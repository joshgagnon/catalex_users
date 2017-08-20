<?php

use App\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ImpersonationControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $seeder = 'DatabaseSeeder';

    /**
     * @test
     */
    public function impersonation()
    {
        // Create admin
        $admin = $this->createUser(['name' => 'admin']);
        $admin->addRole(Role::where('name', '=', 'global_admin')->first());

        // Create non-admin
        $nonAdmin = $this->createUser(['name' => 'non-admin', 'email' => 'non-admin@gmail.com']);

        // Login as admin
        Auth::login($admin);

        // Start impersonation of non admin
        $this->visit('/admin/users')
            ->press('Login');

        // Check we are now impersonating the non-admin user
        $this->seePageIs('/')
            ->see('You are currently impersonating')
            ->see('Return to Admin')
            ->assertEquals($nonAdmin->id, Auth::id());

        // Check we can navigate while impersonating
        $this->visit('/organisation')
            ->assertEquals($nonAdmin->id, Auth::id());

        // Check we can return to the admin user
        $this->press('Return to Admin')
            ->seePageIs('/admin/users')
            ->see('Logged out of')
            ->assertEquals($admin->id, Auth::id());
    }
}
