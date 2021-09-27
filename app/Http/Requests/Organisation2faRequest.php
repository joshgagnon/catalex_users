<?php namespace App\Http\Requests;

use Auth;

use Illuminate\Foundation\Http\FormRequest;

class Organisation2faRequest extends FormRequest {

    public function rules() {
        return [
            'require_2fa' => 'boolean'
        ];
    }

    public function authorize() {
        return Auth::check() && Auth::user()->can('edit_own_organisation');
    }
}
