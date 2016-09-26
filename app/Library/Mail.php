<?php namespace App\Library;

use File;
use Mail as LaravelMail;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class Mail {

	public static function sendStyledMail($view, $data, $receiverEmail, $receiverName, $subject, $attachment=null) {
		$html = view($view, $data)->render();

		$css = File::get(public_path('/css/email.css'));

		$inliner = new CssToInlineStyles($html, $css);
		$markup = $inliner->convert();

		return LaravelMail::send('emails.echo', ['html' => $markup], function($message) use ($receiverEmail, $receiverName, $subject, $attachment) {
			$message->to($receiverEmail, $receiverName)->subject($subject);
			if($attachment) {
				$message->attach($attachment, ['as' => 'invoice.pdf']);
			}
		});
	}

    public static function render($view, $data, $receiverEmail, $receiverName, $subject, $attachment=null) {
        $html = view($view, $data)->render();

        $css = File::get(public_path('/css/email.css'));

        $inliner = new CssToInlineStyles($html, $css);
        $markup = $inliner->convert();
        return $markup;
    }

}
