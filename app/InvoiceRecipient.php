<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceRecipient extends Model
{
    protected $fillable = ['name', 'email'];

    public static $validationRules = [
        'name'  => 'required',
        'email' => 'required|email',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
