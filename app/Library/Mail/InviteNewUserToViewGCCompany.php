<?php

namespace App\Library\Mail;

use App\Library\Mail;

class InviteNewUserToViewGCCompany
{
    private $invitee;
    private $companyName;
    private $inviterName;
    private $loginToken;

    function __construct(User $invitee, $inviterName, $companyName, $loginToken)
    {
        $this->invitee = $invitee;
        $this->companyName = $companyName;
        $this->loginToken = $loginToken;
        $this->inviterName = $inviterName;
    }

    public function send()
    {
        $inviteData = [
            'token'        => $this->loginToken,
            'user'         => $this->invitee,
            'inviter'      => $this->inviterName,
            'name'         => $this->invitee->name,
            'company_name' => $this->companyName,
        ];

        Mail::queueStyledMail('emails.invite-gc', $inviteData, $this->user->email, $this->inviterName, 'You have been invited to Good Companies');
    }
}