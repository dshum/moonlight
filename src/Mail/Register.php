<?php

namespace Moonlight\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Register extends Mailable
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
        $email = $this->scope['email'];

        $this->scope['site'] = $_SERVER['HTTP_HOST'];

        return $this->
            to($email)->
            subject('Регистрация')->
            view('moonlight::mails.register')->with($this->scope);
    }
}