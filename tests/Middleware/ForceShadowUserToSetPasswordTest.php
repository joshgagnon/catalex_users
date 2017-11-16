<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class ForceShadowUserToSetPasswordTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function shadow_user_is_redirected_to_set_password_page()
    {
        $user = $this->createUser(['is_shadow_user' => true]);

        $this->actingAs($user)
            ->visit(route('index'))
            ->seePageIs(route('shadow-user.promote', ['next' => '/']));
    }

    /**
     * @test
     */
    public function non_shadow_users_arent_redirected()
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->visit(route('index'))
            ->seePageIs(route('index'));
    }
}
