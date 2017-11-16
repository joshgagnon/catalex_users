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
}
