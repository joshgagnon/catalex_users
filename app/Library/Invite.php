<?php

namespace App\Library;

use App\FirstLoginToken;
use App\User;

class Invite
{
    public static function sendInvite(User $newUser, $inviter = null)
    {
        $tokenInstance = FirstLoginToken::createToken($newUser);

        $inviteData = [
            'token'   => $tokenInstance->token,
            'user'    => $newUser,
            'inviter' => $inviter,
        ];

        Mail::queueStyledMail('emails.invite', $inviteData, $newUser->email, $newUser->fullName(), 'You have been invited to CataLex');
    }

    /**
     * Return the user for a token - null if that token doesn't exist
     */
    public static function getUser($token)
    {
        $user = null;
        $tokenInstance = FirstLoginToken::where('token', '=', $token)->first();

        if ($tokenInstance && $tokenInstance->isValid()) {
            $user = User::where('id', '=', $tokenInstance->user_id)->first();
        }

        return $user;
    }
}
