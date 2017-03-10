<?php

namespace App\Library;

use App\User;
use App\Library\Mail;
use App\FirstLoginToken;

class Invite
{
    public static function sendInvite(User $newUser, $inviter = null)
    {
        $tokenInstance = FirstLoginToken::createToken($newUser);

        $inviteData = [
            'token' => $tokenInstance->token,
            'user' => $newUser,
            'inviter' => $inviter
        ];

        Mail::queueStyledMail('emails.invite', $inviteData, $newUser->email, $newUser->fullName(), 'You have been invited to CataLex');
    }

    /**
     * Return the user for a token - but make sure the email matches too.
     * Return null if the token and email don't match a user
     */
    public static function getUser($token, $email)
    {
        $user = null;
        $tokenInstance = FirstLoginToken::where('token', '=', $token)->first();

        if ($tokenInstance) {
            $user = User::where('id', '=', $tokenInstance->user_id)
                        ->where('email', '=', $email)
                        ->first();
        }

        return $user;
    }
}
