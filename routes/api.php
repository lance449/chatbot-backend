<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\RoomController;

// Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Room Routes
Route::post('/check-availability', [RoomController::class, 'checkAvailability']);
Route::get('/rooms', [RoomController::class, 'getAvailableRooms']);
Route::post('/assign-room', [RoomController::class, 'assignRoom']); 

// Booking Routes
Route::post('/save-booking', [BookingController::class, 'store']);



