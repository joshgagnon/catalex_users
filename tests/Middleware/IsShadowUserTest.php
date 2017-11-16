<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class IsShadowUserTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function non_shadow_user_redirected_to_index()
    {
        $user = $this->createUser();

        $this->actingAs($user)
             ->visit(route('shadow-user.promote'))
             ->seePageIs(route('index'));
    }

    /**
     * @test
     */
    public function shadow_users_arent_redirected()
    {
        $user = $this->createUser(['is_shadow_user' => true]);

        $this->actingAs($user)
            ->visit(route('shadow-user.promote'))
            ->seePageIs(route('shadow-user.promote'));
    }
}
