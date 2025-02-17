<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function checkAvailability(Request $request)
    {
        try {
            Log::info('Received request:', $request->all());

            $validated = $request->validate([
                'hotel_id' => 'required|integer|exists:hotels,id',
                'check_in' => 'required|date',
                'check_out' => 'required|date|after_or_equal:check_in',
                'room_type' => 'required|string',
                'num_guests' => 'required|integer|min:1',
            ]);

            Log::info('Validated data:', $validated);

            // Check if hotel exists
            $hotel = \App\Models\Hotel::find($validated['hotel_id']);
            if (!$hotel) {
                Log::error('Hotel not found:', ['hotel_id' => $validated['hotel_id']]);
                return response()->json(['error' => 'Hotel not found'], 404);
            }

            // First check if any rooms of this type exist
            $roomCount = Room::where('hotel_id', $validated['hotel_id'])
                ->where('room_type', $validated['room_type'])
                ->count();
            
            Log::info('Total rooms of this type:', ['count' => $roomCount, 'type' => $validated['room_type']]);

            $query = Room::where('hotel_id', $validated['hotel_id'])
                ->where('room_type', $validated['room_type'])
                ->where('capacity', '>=', $validated['num_guests'])
                ->where('is_available', true)
                ->whereNotIn('id', function ($query) use ($validated) {
                    $query->select('room_id')
                        ->from('reservations')
                        ->where(function ($query) use ($validated) {
                            $query->whereBetween('check_in', [$validated['check_in'], $validated['check_out']])
                                ->orWhereBetween('check_out', [$validated['check_in'], $validated['check_out']])
                                ->orWhere(function ($query) use ($validated) {
                                    $query->where('check_in', '<=', $validated['check_in'])
                                        ->where('check_out', '>=', $validated['check_out']);
                                });
                        });
                });

            Log::info('Query:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $availableRooms = $query->get();

            Log::info('Available rooms:', [
                'count' => $availableRooms->count(),
                'rooms' => $availableRooms->toArray()
            ]);

            // If no rooms found, provide more specific error message
            if ($availableRooms->count() === 0) {
                if ($roomCount === 0) {
                    return response()->json(['error' => 'No rooms of type ' . $validated['room_type'] . ' exist in this hotel.']);
                } else {
                    return response()->json(['error' => 'No available rooms found for the selected dates.']);
                }
            }

            return response()->json($availableRooms);
        } catch (\Exception $e) {
            Log::error('Error in checkAvailability:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
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

    public function confirmReservation(Request $request)
    {
        try {
            $validated = $request->validate([
                'hotel_id' => 'required|integer|exists:hotels,id',
                'check_in' => 'required|date',
                'check_out' => 'required|date|after_or_equal:check_in',
                'room_type' => 'required|string',
                'num_guests' => 'required|integer|min:1',
                'customer_name' => 'required|string',
                'customer_email' => 'required|email',
            ]);

            // Find an available room
            $room = Room::where('hotel_id', $validated['hotel_id'])
                ->where('room_type', $validated['room_type'])
                ->where('capacity', '>=', $validated['num_guests'])
                ->where('is_available', true)
                ->first();

            if (!$room) {
                return response()->json(['error' => 'No available rooms found.'], 404);
            }

            // Mark the room as unavailable
            $room->is_available = false;
            $room->save();

            // Create a reservation
            $reservation = new Reservation();
            $reservation->hotel_id = $validated['hotel_id'];
            $reservation->room_id = $room->id;
            $reservation->check_in = $validated['check_in'];
            $reservation->check_out = $validated['check_out'];
            $reservation->num_guests = $validated['num_guests'];
            $reservation->customer_name = $validated['customer_name'];
            $reservation->customer_email = $validated['customer_email'];
            $reservation->save();

            return response()->json([
                'message' => 'Reservation confirmed',
                'room_number' => $room->id,
                'reservation_details' => $reservation
            ]);
        } catch (\Exception $e) {
            Log::error('Error confirming reservation: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Server error. Please try again later.'], 500);
        }
    }

    public function assignRoom(Request $request)
    {
        try {
            Log::info('Received request:', $request->all());

            $validated = $request->validate([
                'hotel_id' => 'required|integer|exists:hotels,id',
                'check_in' => 'required|date',
                'check_out' => 'required|date|after_or_equal:check_in',
                'room_type' => 'required|string',
                'num_guests' => 'required|integer|min:1',
                'customer_name' => 'required|string',
                'customer_email' => 'required|email',
                'customer_phone' => 'required|string',
            ]);

            Log::info('Validated data:', $validated);

            // Find an available room
            $room = Room::where('hotel_id', $validated['hotel_id'])
                ->where('room_type', $validated['room_type'])
                ->where('capacity', '>=', $validated['num_guests'])
                ->where('is_available', true)
                ->first();

            if (!$room) {
                Log::error('No available rooms found:', $validated);
                return response()->json(['error' => 'No available rooms found.'], 404);
            }

            // Mark the room as unavailable
            $room->is_available = false;
            $room->save();

            // Create a reservation
            $reservation = new Reservation();
            $reservation->hotel_id = $validated['hotel_id'];
            $reservation->room_id = $room->id;
            $reservation->check_in = $validated['check_in'];
            $reservation->check_out = $validated['check_out'];
            $reservation->num_guests = $validated['num_guests'];
            $reservation->customer_name = $validated['customer_name'];
            $reservation->customer_email = $validated['customer_email'];
            $reservation->customer_phone = $validated['customer_phone'];
            $reservation->save();

            return response()->json([
                'message' => 'Reservation confirmed',
                'room_number' => $room->id,
                'reservation_details' => $reservation
            ]);
        } catch (\Exception $e) {
            Log::error('Error assigning room: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Server error. Please try again later.'], 500);
        }
    }
    
    public function updateRoomAvailability(Request $request)
    {
        try {
            $validated = $request->validate([
                'room_id' => 'required|integer|exists:rooms,id',
                'available' => 'required|boolean',
            ]);

            $room = Room::find($validated['room_id']);
            $room->is_available = $validated['available'];
            $room->save();

            return response()->json(['message' => 'Room availability updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error updating room availability.'], 500);
        }
    }
}
