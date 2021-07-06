<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    // Login function
    public function login(Request $request)
    {
        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Wrong creditentials'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();

        $token = $user->createToken($request->email);

        return $token;
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'unique:users|required',
            'email'    => 'unique:users|required',
            'password' => 'required',
        ]);

        $name = $request->name;
        $email = $request->email;
        $password = $request->password;
        User::create(['name' => $name, 'email' => $email, 'password' => Hash::make($password)]);
        $user = User::where('email', $request->get('email'))->first();
        if ($request->has('hash')) {
            $invite = Invitation::where('hash', $request->get('hash'))->first();
            if ($invite->status === Invitation::WAITING) {
                $invite->status = Invitation::ACCEPTED;
                $invite->fk_receiver_id = $user->id;
                $invite->save();

                $album = Album::where('id', $invite->fk_album_id)->first();
                $album->users()->attach([$user->id]);
                $album->save();
            }
        }

        return new UserResource($user);
    }

    public function show()
    {
        if (!Auth::user()) {
            return response()->json([
                'message' => 'You must be authenticated'
            ], 401);
        }
        $userId = Auth::user()->id;

        $user = User::whereId($userId)->first();

        return new UserResource($user);
    }

    public function updateProfile(Request $request)
    {
        $userId = Auth::user()->id;

        User::whereId($userId)->update($request->toArray());

        return response()->json([
            'message' => 'Votre nom a bien été modifié !'
        ], 200);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $userId = Auth::user()->id;

        User::whereId($userId)->update(['password' => Hash::make($request->password)]);

        return response()->json([
            'message' => 'Votre mot de passe a bien été modifié !'
        ], 200);
    }

    public function delete()
    {
        $userId = Auth::user()->id;

        User::find($userId)->delete();

        return response()->json([
            'message' => 'Successfully deleted'
        ], 200);
    }

}
