<?php

namespace App\Library;

use App\Service;
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
        $billable = $this->user->getBillableEntity();

        if ($billable->billingExempt() || $this->user->billingExempt()) {
            // User or their billable is exempt from billing - they get all services
            $services = Service::get()->pluck('name')->toArray();
        }
        else if (!$billable->subscriptionUpToDate()) {
            // The user's billing failed, they get only get free services
            $services = Service::where('is_paid_service', false)->get()->pluck('name')->toArray();
        }
        else {
            $services = $this->user->services()->get()->pluck('name')->toArray();
        }

        // Build the rest of the summary
        $userSummary = [
            'id' => $this->user->id,
            'email' => $this->user->email,
            'name' => $this->user->name,
            'free' => $this->user->free,
            'email_verified' => $this->user->email_verified,
            'subscription_up_to_date' => $this->user->subscriptionUpToDate(),
            'roles' => $this->user->roles->pluck('name')->toArray(),
            'services' => $services,
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