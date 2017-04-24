<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrganisationInvite extends Model
{
    protected $fillable = ['invited_user_id', 'inviting_user_id', 'organisation_id'];

    public function invitedUser()
    {
        return $this->belongsTo(User::class, 'invited_user_id');
    }

    public function invitingUser()
    {
        return $this->belongsTo(User::class, 'inviting_user_id');
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
