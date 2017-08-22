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

    public static function sendInviteToView(User $newUser, $companyName, $inviter = null)
    {
        $inviteData = [
            'user'         => $newUser,
            'inviter'      => $inviter,
            'name'         => $newUser->name,
            'company_name' => $companyName,
        ];

        Mail::queueStyledMail('emails.view-gc', $inviteData, $newUser->email, $inviter, 'You have been given access to a Good Companies\' Company');
    }

    public static function sendInviteNewUserToView(User $newUser, $companyName, $inviter = null)
    {
        $tokenInstance = FirstLoginToken::createToken($newUser);

        $inviteData = [
            'token'        => $tokenInstance->token,
            'user'         => $newUser,
            'inviter'      => $inviter,
            'name'         => $newUser->name,
            'company_name' => $companyName,
        ];

        Mail::queueStyledMail('emails.invite-gc', $inviteData, $newUser->email, $inviter, 'You have been invited to Good Companies');
    }

    /**
     * Return the user for a token - null if that token doesn't exist
     */
    public static function getUser($token)
    {
        $user = null;
        $tokenInstance = FirstLoginToken::where('token', '=', $token)->valid()->first();

        if ($tokenInstance) {
            $user = User::where('id', '=', $tokenInstance->user_id)->first();
        }

        return $user;
    }
}
