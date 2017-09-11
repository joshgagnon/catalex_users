<?php

namespace App\Library\Mail;

use App\Library\Mail;
use App\User;

class InviteToSignDocument
{
    private $invitee;
    private $inviterName;
    private $link;
    private $message;

    function __construct(User $invitee, $inviterName, $link, $message)
    {
        $this->invitee = $invitee;
        $this->inviterName = $inviterName;
        $this->link = $link;
        $this->message = $message;
    }

    public function send()
    {
        $inviteData = [
            'inviteeName' => $this->invitee->name,
            'inviterName' => $this->inviterName,
            'link'        => $this->link,
            'message'     => $this->message,
        ];

        Mail::queueStyledMail('emails.sign.invite-existing-user', $inviteData, $this->invitee->email, $this->invitee->name, 'You have been invited to sign a document in CataLex Sign');
    }
}