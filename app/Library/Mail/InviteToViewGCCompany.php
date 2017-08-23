<?php

namespace App\Library\Mail;

use App\Library\Mail;
use App\User;

class InviteToViewGCCompany
{
    private $invitee;
    private $companyName;
    private $inviterName;

    function __construct(User $invitee, $inviterName, $companyName)
    {
        $this->invitee = $invitee;
        $this->companyName = $companyName;
        $this->inviterName = $inviterName;
    }

    public function send()
    {
        $inviteData = [
            'user'         => $this->invitee,
            'inviter'      => $this->inviterName,
            'name'         => $this->invitee->name,
            'company_name' => $this->companyName,
        ];

        Mail::queueStyledMail('emails.view-gc', $inviteData, $this->invitee->email, $this->invitee->name, 'You have been given access to a Good Companies\' Company');
    }
}