<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomController;


Route::post('/check-availability', [RoomController::class, 'checkAvailability']);


Route::get('/', function () {
    return view('welcome');
});
