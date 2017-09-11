<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class HomeControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function email_verification_notification_shows()
    {
        $user = $this->createUser();

        Auth::loginUsingId($user->id);

        $this->visit(route('index'))
            ->see('You need to verify your CataLex email, please click the "Send Verification Email" button to verify your email.');
    }

    /**
     * @test
     */
    public function email_verification_notification_not_displayed_for_verified_user()
    {
        $user = $this->createUser(['email_verified' => true]);

        Auth::loginUsingId($user->id);

        $this->visit(route('index'))
            ->dontSee('You need to verify your CataLex email, please click the "Send Verification Email" button to verify your email.');
    }
}
