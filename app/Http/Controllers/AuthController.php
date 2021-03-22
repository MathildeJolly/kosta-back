<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
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

        $name     = $request->name;
        $email    = $request->email;
        $password = $request->password;
        $user     = User::create(['name' => $name, 'email' => $email, 'password' => Hash::make($password)]);

        return new UserResource($user);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'   => "required|string",
            "password" => "required|string"
        ]);


        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        $token = $user->createToken($request->email);

        return $token;
    }

    public function show()
    {
        $userId = Auth::user()->id;

        $user = User::whereId($userId)->first();

        return new UserResource($user);
    }

    public function updateProfile(Request $request)
    {
        $userId = Auth::user()->id;

        User::whereId($userId)->update($request->toArray());

        return response()->json([
            'message' => 'Successfully updated'
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
            'message' => 'Successfully updated'
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
