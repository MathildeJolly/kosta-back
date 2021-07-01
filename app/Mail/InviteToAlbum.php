<?php

namespace App\Mail;

use App\Models\Album;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteToAlbum extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var Invitation
     */
    private $invitation;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->from(env('MAIL_USERNAME') ? env('MAIL_USERNAME') : "noreply.kosta@gmail.com")
            ->subject("Un invitation à l'album : " . $this->invitation->getAlbum->name . "  de " . $this->invitation->getSender->name)
            ->markdown('mails/invite')->with([
                'invit' => $this->invitation
            ]);
    }
}
