<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'hotel_id' => 'required|integer',
            'user_name' => 'required|string',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
        ]);

        Booking::create([
            'hotel_id' => $request->hotel_id,
            'user_name' => $request->user_name,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
        ]);

        return response()->json(['message' => 'Booking saved successfully!'], 201);
    }
}
