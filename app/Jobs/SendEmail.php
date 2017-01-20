<?php

namespace App\Jobs;

use App\Jobs\Job;
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

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($markup, $receiverEmail, $receiverName, $subject, $attachments)
    {
        $this->markup = $markup;
        $this->receiverEmail = $receiverEmail;
        $this->receiverName = $receiverName;
        $this->subject = $subject;
        $this->attachments = $attachments;
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

        $mailer->send('emails.echo', ['html' => $this->markup], function($message) use ($receiverEmail, $receiverName, $subject, $attachments) {
            $message->to($receiverEmail, $receiverName);
            $message->subject($subject);

            if ($attachments) {
                foreach ($attachments as $attachment) {
                    $message->attach($attachment['path'], ['as' => $attachment['name']]);
                }
            }
        });
    }
}
