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

        // Check we are automatically logged in
        $this->assertEquals($user->id, Auth::id());

        // Check the users password was updated correctly
        Auth::logout();
        $loginSuccess = Auth::attempt(['email' => $user->email, 'password' => $password], false, false);
        $this->assertTrue($loginSuccess);
    }

    /**
     * @test
     */
    public function token_only_valid_for_one_use()
    {
        // Create a user and token
        $user = $this->createUser();
        $tokenRecord = FirstLoginToken::createToken($user);

        // Use the token
        $this->visit(route('first-login.index', $tokenRecord->token))
            ->type('whatever', 'password')
            ->type('whatever', 'password_confirmation')
            ->press('Login');

        // Logout
        Auth::logout();

        // Check that if we try to use the token again, we 404
        $this->get(route('first-login.index', $tokenRecord->token))
            ->assertResponseStatus(404);
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
