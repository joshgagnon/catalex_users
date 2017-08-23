<?php

namespace App\Library\Mail;

use App\Library\Mail;

class InviteToSignDocument
{
    private $invitee;
    private $inviterName;

    function __construct(User $invitee, $inviterName)
    {
        $this->invitee = $invitee;
        $this->inviterName = $inviterName;
    }

    public function send()
    {
        $inviteData = [
            'inviteeName' => $this->invitee->name,
            'inviterName' => $this->inviterName,
        ];

        Mail::queueStyledMail('emails.sign.invite-existing-user', $inviteData, $this->user->email, $this->inviterName, 'You have been invited to sign a document in CataLex Sign');
    }
}