<?php

namespace App\Library\Mail;

use App\Library\Mail;
use App\User;

class InviteToSignDocument
{
    private $invitee;
    private $inviterName;
    private $link;

    function __construct(User $invitee, $inviterName, $link)
    {
        $this->invitee = $invitee;
        $this->inviterName = $inviterName;
        $this->link = $link;
    }

    public function send()
    {
        $inviteData = [
            'inviteeName' => $this->invitee->name,
            'inviterName' => $this->inviterName,
            'link'        => $this->link,
        ];

        Mail::queueStyledMail('emails.sign.invite-existing-user', $inviteData, $this->invitee->email, $this->invitee->name, 'You have been invited to sign a document in CataLex Sign');
    }
}