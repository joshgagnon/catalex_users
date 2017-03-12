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
    public function invite()
    {
        $orgAdmin = $this->createUser();
        $this->createOrganisation([], $orgAdmin);

        Auth::loginUsingId($orgAdmin->id);

        $this->visit('/organisation')
             ->type('Johnny', 'name')
             ->type('johnny@example.com', 'email')
             ->press('Send invitation');

        $this->seePageIs('/organisation')
             ->see('An invite has been sent to johnny@example.com');

        $newUser = User::where('email', '=', 'johnny@example.com')->first();
        $tokenInstance = FirstLoginToken::where('user_id', '=', $newUser->id)->first();

        $this->assertNotNull($tokenInstance);

        Auth::logout();

        $this->visit('/password/first-login/' . $tokenInstance->token)
             ->type('pass123', 'password')
             ->type('pass123', 'password_confirmation')
             ->press('Login');

        $this->assertEquals($newUser->id, Auth::id());
    }
}
