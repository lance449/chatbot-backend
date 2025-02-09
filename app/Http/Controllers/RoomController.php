<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
    public function checkAvailability(Request $request)
    {
        try {
            $validated = $request->validate([
                'hotel_id' => 'required|integer|exists:hotels,id',
                'check_in' => 'required|date',
                'check_out' => 'required|date|after_or_equal:check_in',
                'room_type' => 'required|string',
                'num_guests' => 'required|integer|min:1',
            ]);
    
            // Debugging: Log the validated data
            Log::info('Validated data:', [$validated]);
    
            // Find available rooms based on the criteria
            $availableRooms = Room::where('hotel_id', $validated['hotel_id'])
                ->where('room_type', $validated['room_type'])
                ->where('capacity', '>=', $validated['num_guests'])
                ->get();
    
            // Debugging: Log the SQL query and results
            Log::info('SQL Query:', [Room::where('hotel_id', $validated['hotel_id'])
                ->where('room_type', $validated['room_type'])
                ->where('capacity', '>=', $validated['num_guests'])
                ->toSql()]);
    
            Log::info('Available rooms:', [$availableRooms]);
    
            return response()->json($availableRooms);
        } catch (\Exception $e) {
            // Error logging
            Log::error('Error checking room availability: ' . $e->getMessage());
            return response()->json(['error' => 'Server error. Please try again later.'], 500);
        }
    }
    

    public function getAvailableRooms(Request $request)
    {
        $request->validate([
            'hotel_id' => 'required|integer',
            'room_type' => 'required|string',
            'guests' => 'required|integer|min:1'
        ]);

        $availableRooms = Room::where('hotel_id', $request->hotel_id)
            ->where('room_type', $request->room_type)
            ->where('capacity', '>=', $request->guests)
            ->whereNotIn('id', function ($query) {
                $query->select('room_id')
                    ->from('reservations')
                    ->whereRaw('NOW() BETWEEN check_in AND check_out'); // Check if room is currently booked
            })
            ->get();

        return response()->json(['availableRooms' => $availableRooms]);
    }
}
