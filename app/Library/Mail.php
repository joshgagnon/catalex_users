<?php namespace App\Library;

use Log;
use File;
use Queue;
use Mail as LaravelMail;
use App\Jobs\SendEmail;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class Mail
{
    public static function buildView($view, $data)
    {
        // Render the view
        $html = view($view, $data)->render();

        // Get the CSS
        $css = File::get(public_path('/css/email.css'));

        // Add the CSS to the view
        $inliner = new CssToInlineStyles($html, $css);
        $markup = $inliner->convert();

        // Return the resulting markup
        return $markup;
    }

    public static function queueStyledMail($view, $data, $receiverEmail, $receiverName, $subject, $attachments=null, $senderName=null, $senderEmail=null)
    {
        $markup = self::buildView($view, $data);

        Queue::push(new SendEmail($markup, $receiverEmail, $receiverName, $subject, $attachments, $senderName, $senderEmail));

        Log::info("Mail [$subject] QUEUED to $receiverEmail");
    }

    public static function render($view, $data, $receiverEmail, $receiverName, $subject, $attachment=null) {
        $html = view($view, $data)->render();

        $css = File::get(public_path('/css/email.css'));

        $inliner = new CssToInlineStyles($html, $css);
        $markup = $inliner->convert();
        return $markup;
    }
}
