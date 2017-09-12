<?php

use App\Library\UserSummariser;
use App\Service;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserSummariserTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function summarise_noOrganisation()
    {
        // Create the user and the organisation
        $user = $this->createUser();

        // Add some services to the user
        $service1 = Service::find(1);
        $service2 = Service::find(2);

        $user->syncSubscriptions([$service1->id, $service2->id]);

        $expected = [
            'id'                      => $user->id,
            'email'                   => $user->email,
            'name'                    => $user->name,
            'free'                    => $user->free,
            'subscription_up_to_date' => true,
            'email_verified'          => false,
            'roles'                   => ['registered_user'],
            'services'                => [$service1->name, $service2->name],
        ];

        $actual = (new UserSummariser($user))->summarise();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function summarise()
    {
        // Create the user and the organisation
        $service1 = Service::find(1);
        $service2 = Service::find(2);

        $user = $this->createUser([], [$service1->id, $service2->id]);
        $org = $this->createOrganisation([], $user);

        // Add some more members to the organisation
        $orgMember2 = $this->createUser(['name' => 'Org member 2', 'email' => '2@org.com', 'organisation_id' => $org->id]);
        $orgMember3 = $this->createUser(['name' => 'Org member 3', 'email' => '3@org.com', 'organisation_id' => $org->id]);

        $expected = [
            'id'                      => $user->id,
            'email'                   => $user->email,
            'name'                    => $user->name,
            'free'                    => $user->free,
            'email_verified'          => false,
            'subscription_up_to_date' => $user->subscriptionUpToDate(),
            'roles'                   => ['organisation_admin', 'registered_user'],
            'services'                => [$service1->name, $service2->name],
            'organisation'            => [
                'organisation_id' => $org->id,
                'name'            => $org->name,
                'members'         => [
                    [
                        'id'    => $user->id,
                        'email' => $user->email,
                        'name'  => $user->name,
                        'roles' => ['organisation_admin', 'registered_user'],
                    ],
                    [

                        'id'    => $orgMember2->id,
                        'email' => $orgMember2->email,
                        'name'  => $orgMember2->name,
                        'roles' => ['registered_user'],
                    ],
                    [

                        'id'    => $orgMember3->id,
                        'email' => $orgMember3->email,
                        'name'  => $orgMember3->name,
                        'roles' => ['registered_user'],
                    ],
                ],
            ],
        ];

        $actual = (new UserSummariser($user))->summarise();

        $this->assertEquals($expected, $actual);
    }
}
