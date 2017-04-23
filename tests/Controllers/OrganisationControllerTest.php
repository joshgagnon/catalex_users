<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;
use App\FirstLoginToken;

class OrganisationControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function invite_new_user()
    {
        // Create an organisation and an admin for it
        $orgAdmin = $this->createUser();
        $this->createOrganisation([], $orgAdmin);

        // Log in as the org admin
        Auth::loginUsingId($orgAdmin->id);

        // Invite a new user using and email address
        $this->visit('/organisation')
             ->type('Johnny', 'name')
             ->type('johnny@example.com', 'email')
             ->press('Send invitation');

        // Check the invite form takes us back to the organisation page with a success message
        $this->seePageIs('/organisation')
             ->see('An invite has been sent to johnny@example.com');

        // Find the user we just created
        $newUser = User::where('email', '=', 'johnny@example.com')->first();

        // Check a first login token was created for the new user
        $tokenInstance = FirstLoginToken::where('user_id', '=', $newUser->id)->first();
        $this->assertNotNull($tokenInstance);

        // Check the new user was added to the organisation
        $this->assertEquals($orgAdmin->organisation_id, $newUser->organisation_id);
    }

    /**
     * @test
     */
    public function invite_existing_user()
    {
        // Create an organisation and an admin for it
        $orgAdmin = $this->createUser();
        $org = $this->createOrganisation([], $orgAdmin);

        // Log in as the org admin
        Auth::loginUsingId($orgAdmin->id);

        // Create user without an org - to be invited to the org
        $joiningUser = $this->createUser(['name' => 'user two', 'email' => 'user2@gmail.com']);

        // Invite a new user using and email address
        $this->visit('/organisation')
            ->type('Anything you want', 'name')
            ->type($joiningUser->email, 'email')
            ->press('Send invitation');

        // Check the invite form takes us back to the organisation page with a success message
        $this->seePageIs('/organisation')
            ->see('An invite has been sent to ' . $joiningUser->email);

        // Check a first login token was created for the joining user
        $loginToken = FirstLoginToken::where('user_id', $joiningUser->id);
        $this->assertNotNull($loginToken);
    }
}
