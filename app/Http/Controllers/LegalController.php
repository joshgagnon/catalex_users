<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class LegalController extends Controller {



	/**
	 * Show the privacy policy
	 *
	 * @return Response
	 */
	public function privacypolicy()
	{
		return view('privacypolicy');
	}

		/**
	 * Show the customer agreement
	 *
	 * @return Response
	 */
	public function customeragreement()
	{
		return view('customeragreement');
	}

		/**
	 * Show the terms
	 *
	 * @return Response
	 */
	public function termsofuse()
	{
		return view('termsofuse');
	}
}
