<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/testExif', function(){
    dd(exif_read_data(public_path() . '/medias/195/197747713_191118862799518_2340844925558268762_n.jpg', 0, true)['FILE']['FileDateTime']);
});
Route::get('/album/join/{hash}', [\App\Http\Controllers\AlbumController::class, 'join']);
Route::get('/album/decline/{hash}', [\App\Http\Controllers\AlbumController::class, 'decline']);
