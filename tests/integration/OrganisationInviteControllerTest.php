<?php

use App\OrganisationInvite;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrganisationInviteControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function createInvite($org, $inviter, $invitee)
    {
        return OrganisationInvite::create([
            'organisation_id' => $org->id,
            'inviting_user_id' => $inviter->id,
            'invited_user_id' => $invitee->id,
        ]);
    }

    /**
     * @test
     */
    public function see_invite_on_homepage()
    {
        // Create an org
        $orgAdmin = $this->createUser(['email' => 'org@admin.com']);
        $org = $this->createOrganisation([], $orgAdmin);

        // Create a user to invite and login as them
        $user = $this->createUser(['email' => 'user@catalex.nz']);
        Auth::loginUsingId($user->id);

        // Invite the user to the org
        $this->createInvite($org, $orgAdmin, $user);

        // See the invite
        $this->visit('/')
            ->see('You have a pending organisation invite.')
            ->click('View invite')
            ->see('Invite to:')
            ->see($org->name);
    }

    /**
     * @test
     */
    public function accept_invite()
    {
        // Create an org
        $orgAdmin = $this->createUser(['email' => 'org@admin.com']);
        $org = $this->createOrganisation([], $orgAdmin);

        // Create a user to invite and login as them
        $user = $this->createUser(['email' => 'user@catalex.nz']);
        Auth::loginUsingId($user->id);

        // Invite the user to the org
        $invite = $this->createInvite($org, $orgAdmin, $user);

        // Join
        $this->visit(route('organisation-invites.index'))
            ->press('Join')
            ->seePageIs(route('index'))
            ->see('Successfully joined the organisation: ' . $org->name);

        // Check the user has joined the org
        $user = $user->fresh();
        $this->assertEquals($org->id, $user->organisation_id);

        // Check the invite is deleted after it is accepted
        $invite = $invite->fresh();
        $this->assertNull($invite);
    }

    /**
     * @test
     */
    public function dismiss_invite()
    {
        // Create an org
        $orgAdmin = $this->createUser(['email' => 'org@admin.com']);
        $org = $this->createOrganisation([], $orgAdmin);

        // Create a user to invite and login as them
        $user = $this->createUser(['email' => 'user@catalex.nz']);
        Auth::loginUsingId($user->id);

        // Invite the user to the org
        $invite = $this->createInvite($org, $orgAdmin, $user);

        // Join
        $this->visit(route('organisation-invites.index'))
            ->press('Dismiss')
            ->seePageIs(route('index'))
            ->see('Organisation invite deleted.');

        // Check the user is still org-less
        $user = $user->fresh();
        $this->assertNull($user->organisation_id);

        // Check the invite has been deleted
        $invite = $invite->fresh();
        $this->assertNull($invite);
    }
}
