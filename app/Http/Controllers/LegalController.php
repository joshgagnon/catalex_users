<?php namespace App\Http\Controllers;

class LegalController extends Controller
{
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
	 * Show the terms
	 *
	 * @return Response
	 */
	public function termsofuse()
	{
		return view('termsofuse');
	}
}
