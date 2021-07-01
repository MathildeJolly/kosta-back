<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    protected $table = "cms_invitations";
    const WAITING = "WAITING";
    const ACCEPTED = "ACCEPTED";
    const DECLINE = "DECLINE";

    public function getSender()
    {
        return $this->hasOne(User::class, 'id', 'fk_sender_id');
    }

    public function getReceiver()
    {
        return $this->hasOne(User::class, 'id', 'fk_receiver_id');
    }

    public function getAlbum()
    {
        return $this->hasOne(Album::class, 'id', 'fk_album_id');
    }
}
