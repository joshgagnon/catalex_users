<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Config;
use Carbon\Carbon;

class FirstLoginToken extends Model
{
    public static function createToken(User $user)
    {
        // Delete any existing tokens
        FirstLoginToken::where('user_id', '=', $user->id)->delete();

        // Create a new instance with a random token
        return FirstLoginToken::forceCreate([
            'user_id' => $user->id,
            'token' => hash_hmac('sha256', Str::random(100), Config::get('APP_KEY'))
        ]);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeValid($query)
    {
        return $query;

        // TODO: Add expiry to tokens
        // This means we need a page to tell people if their token has expired and
        // a way to create another token (probably by the org admins)

        // $tokenValidFor = config('auth.first_login.expire');
        // $minValidDate = Carbon::now()->subSeconds($tokenValidFor);

        // return $query->where('created_at', '>', $minValidDate);
    }
}
