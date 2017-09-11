<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Config;

class EmailVerificationToken extends Model
{
    public static function createToken(User $user)
    {
        // Delete any existing tokens
        EmailVerificationToken::where('user_id', '=', $user->id)->delete();

        // Create a new instance with a random token
        return EmailVerificationToken::forceCreate([
            'user_id' => $user->id,
            'token' => hash_hmac('sha256', Str::random(100), Config::get('APP_KEY'))
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
