<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CreateUserMail extends Mailable
{
    use Queueable, SerializesModels;

    private $email;
    private $invite;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $invite)
    {
        $this->email = $email;
        $this->invite = $invite;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(env('MAIL_USERNAME') ? env('MAIL_USERNAME') : "noreply.kosta@gmail.com")
            ->subject("Un invitation à crée votre compte et à rejoindre un  album ")
            ->markdown('mails/createUser')->with([
                'email' => $this->email, 'invite' => $this->invite
            ]);
    }
}
