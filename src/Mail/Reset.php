<?php

namespace Moonlight\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Reset extends Mailable
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
        $login = $this->scope['login'];
        $email = $this->scope['email'];
        $token = $this->scope['token'];

        $url = route('moonlight.reset.create').'?login='.$login.'&token='.$token;

        $this->scope['site'] = $_SERVER['HTTP_HOST'];
        $this->scope['url'] = $url;

        return $this->
            to($email)->
            subject('Сброс пароля')->
            view('moonlight::mails.reset')->with($this->scope);
    }
}