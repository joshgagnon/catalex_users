<?php

namespace App\Library\Mail;

use App\Library\Mail;
use App\User;

class InviteNewUserToSignDocument
{
    private $invitee;
    private $inviterName;
    private $link;
    private $loginToken;

    function __construct(User $invitee, $inviterName, $link, $loginToken)
    {
        $this->invitee = $invitee;
        $this->inviterName = $inviterName;
        $this->link = $link;
        $this->loginToken = $loginToken;
    }

    public function send()
    {
        $inviteData = [
            'inviteeName' => $this->invitee->name,
            'inviterName' => $this->inviterName,
            'token'       => $this->loginToken,
            'link'        => $this->link,
        ];

        Mail::queueStyledMail('emails.sign.invite-new-user', $inviteData, $this->invitee->email, $this->invitee->name, 'You have been invited to sign a document in CataLex Sign');
    }
}