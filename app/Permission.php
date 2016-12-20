<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model {

    public static $rules = [
        'name' => 'required|unique|between:3,128',
        'display_name' => 'required|between:3,128',
    ];

    public function roles()
    {
        return $this->belongsToMany('App\Role');
    }
}
