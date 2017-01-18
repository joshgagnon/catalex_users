<?php namespace App\Library;

use Log;
use File;
use Mail as LaravelMail;
use App\Jobs\SendMail;
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

    public static function queueStyledMail($view, $data, $receiverEmail, $receiverName, $subject, $attachments=null)
    {
        $markup = self::buildView($view, $data);

        \Queue::push(function($job) use ($markup, $receiverEmail, $receiverName, $subject, $attachments) {

            LaravelMail::send('emails.echo', ['html' => $markup], function($message) use ($receiverEmail, $receiverName, $subject, $attachments) {
                $message->to($receiverEmail, $receiverName);
                $message->subject($subject);

                if ($attachments) {
                    foreach ($attachments as $attachment) {
                        $message->attach($attachment['path'], ['as' => $attachment['name']]);
                    }
                }
            });
        });

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
