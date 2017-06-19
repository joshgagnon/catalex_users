<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrganisationAdminControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function can_remove_user_from_org()
    {
        // Create the org with an admin
        $orgAdmin = $this->createUser(['email' => 'admin@admin.com']);
        $org = $this->createOrganisation([], $orgAdmin);

        $userToRemove = $this->createUser(['email' => 'delete@delete.com', 'organisation_id' => $org->id]);

        // Login as the org admin
        Auth::loginUsingId($orgAdmin->id);

        // Remove the user
        $this->visit(route('organisation.index'))
            ->press('Remove')
            ->see($userToRemove->name . ' removed from organisation.');

        // Get a fresh copy of the user to remove
        $userToRemove = $userToRemove->fresh();

        // Check the user to remove was removed from the org
        $this->assertNull($userToRemove->organisation_id);
    }
}
