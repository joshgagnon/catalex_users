<?php

use App\FirstLoginToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FirstLoginControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function use_first_login_token()
    {
        // Create a user and a token for them to login with
        $user = $this->createUser();
        $tokenRecord = FirstLoginToken::createToken($user);

        $password = 'pass123';

        // Use the first login token to set a password
        $this->visit(route('first-login.index', $tokenRecord->token))
            ->type($password, 'password')
            ->type($password, 'password_confirmation')
            ->press('Login')
            ->seePageIs(route('index'))
            ->see('Password set');

        // Grab a fresh copy of the user, as the controller made changes to it
        $user = $user->fresh();

        // Check we are automatically logged in
        $this->assertEquals($user->id, Auth::id());

        // Check the users password was updated correctly
        Auth::logout();
        $loginSuccess = Auth::attempt(['email' => $user->email, 'password' => $password], false, false);
        $this->assertTrue($loginSuccess);

        // Check the process verified the users email
        $this->assertTrue($user->email_verified);
    }

    /**
     * @test
     */
    public function invalid_token_returns_404()
    {
        $this->get(route('first-login.index', 'Some_non-existent_token'))
            ->assertResponseStatus(404);
    }
}
