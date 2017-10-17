<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceRecipient extends Model
{
    protected $fillable = ['email', 'name'];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
