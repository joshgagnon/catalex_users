<?php namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BaseRequest extends FormRequest {

	/**
	 * For requests where user is authenticated but doesn't have correct
	 * permissions, show a permission denied page.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function forbiddenResponse() {
		return response()->view('auth.denied');
	}
}
