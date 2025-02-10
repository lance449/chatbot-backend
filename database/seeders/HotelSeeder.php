<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\Room;

class HotelSeeder extends Seeder
{
    public function run()
    {
        $hotels = [
            ['name' => 'Hotel A', 'description' => 'Luxury and comfort in the city.', 'logo' => '/hotel-a.png'],
            ['name' => 'Hotel B', 'description' => 'A beachfront paradise.', 'logo' => '/hotel-b.png'],
            ['name' => 'Hotel C', 'description' => 'A cozy retreat near nature.', 'logo' => '/hotel-c.png'],
        ];

        // Define fixed prices and capacity ranges for each room type
        $roomTypes = [
            'Standard' => [
                'price' => 200,
                'min_capacity' => 1,
                'max_capacity' => 2
            ],
            'Family' => [
                'price' => 500,
                'min_capacity' => 3,
                'max_capacity' => 6
            ],
            'Suite' => [
                'price' => 800,
                'min_capacity' => 2,
                'max_capacity' => 6
            ]
        ];

        foreach ($hotels as $hotelData) {
            $hotel = Hotel::create($hotelData);

            // Create rooms with fixed prices per type
            foreach ($roomTypes as $type => $details) {
                for ($i = 1; $i <= 10; $i++) { // 10 rooms per type
                    // Generate random capacity within the allowed range
                    $capacity = rand($details['min_capacity'], $details['max_capacity']);
                    
                    Room::create([
                        'hotel_id' => $hotel->id,
                        'room_type' => $type,
                        'capacity' => $capacity, // Random capacity within range
                        'price' => $details['price'],
                        'is_available' => true,
                    ]);
                }
            }
        }
    }
}
