<?php

namespace App\Http\Controllers;

use App\Mail\CreateUserMail;
use App\Mail\InviteToAlbum;
use App\Models\Invitation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function returnJsonErreur($message, $code = 500)
    {
        return response()->json([
            'message' => $message
        ], $code);
    }


    public function sendMailForCreateUser($email, $album)
    {
        $invitation = new Invitation();
        $invitation->fk_sender_id = auth()->user()->id;
        $invitation->fk_receiver_id = null;
        $invitation->fk_album_id = $album->id;
        $invitation->hash = $this->unique_random((new Invitation())->getTable(), 'hash', 32);
        $invitation->save();

        Mail::to($email)->send(new CreateUserMail($email, $invitation));


    }

    public function unique_random(string $table, string $col, int $chars = 16): string
    {

        $unique = false;

        // Store tested results in array to not test them again
        $tested = [];

        do {

            // Generate random string of characters
            $random = Str::random($chars);

            // Check if it's already testing
            // If so, don't query the database again
            if (in_array($random, $tested)) {
                continue;
            }

            // Check if it is unique in the database
            $count = DB::table($table)->where($col, '=', $random)->count();

            // Store the random character in the tested array
            // To keep track which ones are already tested
            $tested[] = $random;

            // String appears to be unique
            if ($count == 0) {
                // Set unique to true to break the loop
                $unique = true;
            }

            // If unique is still false at this point
            // it will just repeat all the steps until
            // it has generated a random string of characters

        } while (!$unique);


        return $random;
    }
}
