<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/tokens/create', function (Request $request) {
    $token = $request->user()->createToken($request->token_name);

    return ['token' => $token->plainTextToken];
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user/show', [AuthController::class, "show"]);
    Route::patch('user/update/profile', [AuthController::class, "updateProfile"]);
    Route::patch('user/update/password', [AuthController::class, "updatePassword"]);
    Route::delete('user/delete', [AuthController::class, "delete"]);

    Route::post('album', [AlbumController::class, "store"]);
    Route::post('album/{id}/file', [AlbumController::class, "storeFileForAlbum"]);

    Route::get('albums', [AlbumController::class, "all"]);
});

Route::post('login', [AuthController::class, "login"]);
Route::post('logout', [AuthController::class, "logout"]);
Route::post('register', [AuthController::class, "register"]);

Route::get('albums/{slug}', [AlbumController::class, "show"]);
