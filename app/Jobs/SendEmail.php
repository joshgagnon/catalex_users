<?php

namespace App\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Mail\Mailer;

class SendEmail extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $markup;
    protected $receiverEmail;
    protected $receiverName;
    protected $subject;
    protected $attachments;
    protected $senderName;
    protected $senderEmail;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($markup, $receiverEmail, $receiverName, $subject, $attachments, $senderName, $senderEmail)
    {
        $this->markup = $markup;
        $this->receiverEmail = $receiverEmail;
        $this->receiverName = $receiverName;
        $this->subject = $subject;
        $this->attachments = $attachments;
        $this->senderName = $senderName;
        $this->senderEmail = $senderEmail;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        $receiverEmail = $this->receiverEmail;
        $receiverName = $this->receiverName;
        $subject = $this->subject;
        $attachments = $this->attachments;
        $senderName = $this->senderName;
        $senderEmail = $this->senderEmail;

        $mailer->send('emails.echo', ['html' => $this->markup], function($message) use ($receiverEmail, $receiverName, $subject, $attachments, $senderName, $senderEmail) {
            $message->to($receiverEmail, $receiverName);
            $message->subject($subject);

            if ($senderName && $senderEmail) {
                $message->replyTo($senderEmail, $senderName);
            }

            if ($attachments) {
                foreach ($attachments as $attachment) {
                    $message->attach($attachment['path'], ['as' => $attachment['name']]);
                }
            }
        });
    }
}
