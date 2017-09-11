<?php

namespace App\Library\Mail;

use App\Library\Mail;

class EmailVerification
{
    private $name;
    private $email;
    private $token;

    function __construct(string $name, string $email, string $token)
    {
        $this->name = $name;
        $this->email = $email;
        $this->token = $token;
    }

    public function send()
    {
        $data = [
            'name' => $this->name,
            'link' => route('email-verification.verify', $this->token)
        ];

        Mail::queueStyledMail('emails.email-verification', $data, $this->email, $this->name, 'CataLex Email Verification');
    }
}