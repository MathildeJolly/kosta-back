<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
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

        $user = User::where('email', $request->email)->firstOrFail();

        $token = $user->createToken($request->email);

        return $token;
    }
}
