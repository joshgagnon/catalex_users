<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;
use App\FirstLoginToken;

class OrganisationControllerTest extends TestCase
{
    use DatabaseTransactions;

    // /**
    //  * @test
    //  */
    // public function invite()
    // {
    //     // Create an organisation and an admin for it
    //     $orgAdmin = $this->createUser();
    //     $this->createOrganisation([], $orgAdmin);

    //     // Log in as the org admin
    //     Auth::loginUsingId($orgAdmin->id);

    //     // Invite a new user using and email address
    //     $this->visit('/organisation')
    //          ->type('Johnny', 'name')
    //          ->type('johnny@example.com', 'email')
    //          ->press('Send invitation');

    //     // Check the invite form takes us back to the organisation page with a success message
    //     $this->seePageIs('/organisation')
    //          ->see('An invite has been sent to johnny@example.com');

    //     // All done with the organisation admin user, time to logout
    //     Auth::logout();

    //     // Find the user we just created, and grab the first login token that was just created for them
    //     $newUser = User::where('email', '=', 'johnny@example.com')->first();
    //     $tokenInstance = FirstLoginToken::where('user_id', '=', $newUser->id)->first();

    //     // Check the new user was added to the correct organisation
    //     $this->assertEquals($orgAdmin->organisation_id, $newUser->organisation_id);

    //     // Check that a first login token was actually created
    //     $this->assertNotNull($tokenInstance);

    //     // Use the first login token to set a password
    //     $this->visit('/password/first-login/' . $tokenInstance->token)
    //          ->type('pass123', 'password')
    //          ->type('pass123', 'password_confirmation')
    //          ->press('Login')
    //          ->see('Password set');

    //     // Check the first login token worked and logged us in
    //     $this->assertEquals($newUser->id, Auth::id());
    // }

    // /**
    //  * @test
    //  */
    // public function edit()
    // {
    //     $orgAdmin = $this->createUser();
    //     $org = $this->createOrganisation([], $orgAdmin);

    //     Auth::loginUsingId($orgAdmin->id);

    //     $newOrgName = 'New Org Name';

    //     // Check the button is there on the overview page
    //     $this->visit('/organisation')
    //          ->see($org->name)
    //          ->click('Edit Organisation')
    //          ->seePageIs(action('OrganisationController@edit', $org->id));

    //     // Check the form, redirect, and success message works
    //     $this->see('Edit ' + $org->name)
    //          ->type($newOrgName, 'name')
    //          ->press('Update')
    //          ->seePageIs('/organisation')
    //          ->see('Organisation name updated')
    //          ->see($newOrgName);

    //      // Refresh our copy of the organisation
    //     $org = $org->fresh();

    //     // Check the name was actually updated
    //     $this->assertEquals($newOrgName, $org->name);
    // }

    /**
     * @test
     */
    public function nonOrgAdminCantEditOrganisation()
    {
        // Create the organisation
        $org = $this->createOrganisation();

        // Create an non-admin org member and login as them
        $orgMember = $this->createUser(['email' => 'johnny@notreal.com', 'organisation_id' => $org->id]);
        Auth::loginUsingId($orgMember->id);

        $this->get(action('OrganisationController@edit', $org->id))
             ->assertResponseStatus(403);

        // dd(action('OrganisationController@update', $org->id));
        $this->call('PUT', action('OrganisationController@update', $org->id), ['name' => 'New name for org'])
             ->assertResponseStatus(403);
    }
}
