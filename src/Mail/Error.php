<?php

namespace Moonlight\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Error extends Mailable
{
    use Queueable, SerializesModels;

    protected $scope;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($scope = [])
    {
        $this->scope = $scope;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $recipients = explode(',', $this->scope['to']);

        $to = array_shift($recipients);
        $cc = $recipients;
        $subject = $this->scope['subject'];

        $mail = $this->to($to);

        if ($cc) {
            $mail->cc($cc);
        }

        return $mail
                ->subject($subject)
                ->view('moonlight::mails.error')
                ->with($this->scope);
    }
}
