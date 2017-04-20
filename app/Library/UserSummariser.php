<?php

namespace App\Library;

use App\User;

class UserSummariser
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function summarise()
    {
        $this->user->load('roles');

        // Build the rest of the summary
        $userSummary = [
            'id' => $this->user->id,
            'email' => $this->user->email,
            'name' => $this->user->name,
            'free' => $this->user->free,
            'subscription_up_to_date' => $this->user->subscriptionUpToDate(),
            'roles' => $this->user->roles->pluck('name')->toArray(),
            'services' => [
                'Good Companies',
                '...',
            ]
        ];

        if ($this->user->organisation) {
            $this->user->load('organisation.members.roles');

            $membersSummary = [];

            foreach ($this->user->organisation->members as $member) {
                $membersSummary[] = [
                    'id' => $member->id,
                    'email' => $member->email,
                    'name' => $member->name,
                    'roles' => $member->roles->pluck('name')->toArray(),
                ];
            }

            $userSummary['organisation'] = [
                'organisation_id' => $this->user->organisation->id,
                'name' => $this->user->organisation->name,
                'members' => $membersSummary,
            ];
        }

        // return the summary
        return $userSummary;
    }
}