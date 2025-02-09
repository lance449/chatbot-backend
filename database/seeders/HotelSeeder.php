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

        $roomTypes = ['Standard', 'Suite', 'Family'];

        foreach ($hotels as $hotelData) {
            $hotel = Hotel::create($hotelData);

            // Create rooms with different types
            foreach ($roomTypes as $roomType) {
                for ($i = 1; $i <= 10; $i++) { // 10 rooms per type
                    Room::create([
                        'hotel_id' => $hotel->id,
                        'room_type' => $roomType,
                        'capacity' => $roomType === 'Family' ? rand(3, 6) : rand(1, 4),
                        'price' => $roomType === 'Suite' ? rand(500, 1000) : rand(100, 500),
                        'available' => true,
                    ]);
                }
            }
        }
    }
}
