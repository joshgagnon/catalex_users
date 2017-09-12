<?php

use App\EmailVerificationToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class EmailVerificationControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function send_email_creates_token()
    {
        $user = $this->createUser();

        Auth::loginUsingId($user->id);

        $this->visit(route('index'))
            ->click('Send Verification Email')
            ->see('Email verification sent, please check your emails.');

        $tokenCreated = $user->emailVerificationToken()->exists();
        $this->assertTrue($tokenCreated);
    }

    /**
     * @test
     */
    public function send_email_deletes_any_existing_tokens()
    {
        $user = $this->createUser();
        Auth::loginUsingId($user->id);

        // Create a token
        EmailVerificationToken::createToken($user);

        // Create a second token
        $this->visit(route('index'))
            ->click('Send Verification Email')
            ->see('Email verification sent, please check your emails.');

        $nTokens = $user->emailVerificationToken()->count();
        $this->assertEquals($nTokens, 1);
    }

    /**
     * @test
     */
    public function send_email_exits_early_if_user_is_already_verified()
    {
        $user = $this->createUser(['email_verified' => true]);

        Auth::loginUsingId($user->id);

        $this->visit(route('email-verification.send-email'))
            ->see('Email is already verified.');
    }

    /**
     * @test
     */
    public function verify_verifies_valid_token()
    {
        $user = $this->createUser();
        $tokenInstance = EmailVerificationToken::createToken($user);

        Auth::loginUsingId($user->id);

        $this->visit(route('email-verification.verify', $tokenInstance->token))
            ->see('Email verified.');

        // Check token was deleted
        $tokenCreated = $user->emailVerificationToken()->exists();
        $this->assertFalse($tokenCreated);

        // Check email is now verified
        $user = $user->fresh(); // grab a fresh copy of the user
        $this->assertTrue($user->email_verified);
    }

    /**
     * @test
     */
    public function verify_rejects_invalid_token()
    {
        $user = $this->createUser();
        EmailVerificationToken::createToken($user);

        Auth::loginUsingId($user->id);

        $this->visit(route('email-verification.verify', 'someinvalidtoken'))
            ->see('Failed to verify email: token did not match email.');

        // Check email is not verified
        $user = $user->fresh(); // grab a fresh copy of the user
        $this->assertFalse($user->email_verified);
    }

    /**
     * @test
     */
    public function verify_rejects_non_existant_token()
    {
        $user = $this->createUser();

        Auth::loginUsingId($user->id);

        $this->visit(route('email-verification.verify', 'sometokenthatdoesntexist'))
            ->see('Failed to verify email: token did not match email.');

        // Check email is not verified
        $user = $user->fresh(); // grab a fresh copy of the user
        $this->assertFalse($user->email_verified);
    }
}
