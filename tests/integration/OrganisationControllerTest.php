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

        // All done with the organisation admin user, time to logout
        Auth::logout();

        // Find the user we just created, and grab the first login token that was just created for them
        $newUser = User::where('email', '=', 'johnny@example.com')->first();
        $tokenInstance = FirstLoginToken::where('user_id', '=', $newUser->id)->first();

        // Check the new user was added to the correct organisation
        $this->assertEquals($orgAdmin->organisation_id, $newUser->organisation_id);

        // Check that a first login token was actually created
        $this->assertNotNull($tokenInstance);

        // Use the first login token to set a password
        $this->visit('/password/first-login/' . $tokenInstance->token)
             ->type('pass123', 'password')
             ->type('pass123', 'password_confirmation')
             ->press('Login')
             ->see('Password set');

        // Check the first login token worked and logged us in
        $this->assertEquals($newUser->id, Auth::id());
    }

//    /**
//     * test
//     */
//    public function invite_existing_user()
//    {
//
//        // Create an organisation and an admin for it
//        $orgAdmin = $this->createUser();
//        $this->createOrganisation([], $orgAdmin);
//
//        // Log in as the org admin
//        Auth::login($orgAdmin);
//
//        // Create user without an org - to be invited to the org
//        $user = $this->createUser([''])
//
//        // Invite a new user using and email address
//        $this->visit('/organisation')
//            ->type('Johnny', 'name')
//            ->type('johnny@example.com', 'email')
//            ->press('Send invitation');
//
//        // Check the invite form takes us back to the organisation page with a success message
//        $this->seePageIs('/organisation')
//            ->see('An invite has been sent to johnny@example.com');
//
//        // All done with the organisation admin user, time to logout
//        Auth::logout();
//
//        // Find the user we just created, and grab the first login token that was just created for them
//        $newUser = User::where('email', '=', 'johnny@example.com')->first();
//        $tokenInstance = FirstLoginToken::where('user_id', '=', $newUser->id)->first();
//
//        // Check the new user was added to the correct organisation
//        $this->assertEquals($orgAdmin->organisation_id, $newUser->organisation_id);
//
//        // Check that a first login token was actually created
//        $this->assertNotNull($tokenInstance);
//
//        // Use the first login token to set a password
//        $this->visit('/password/first-login/' . $tokenInstance->token)
//            ->type('pass123', 'password')
//            ->type('pass123', 'password_confirmation')
//            ->press('Login')
//            ->see('Password set');
//
//        // Check the first login token worked and logged us in
//        $this->assertEquals($newUser->id, Auth::id());
//    }
}
