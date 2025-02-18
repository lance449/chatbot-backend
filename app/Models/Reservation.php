<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'room_id', 
        'check_in', 
        'check_out', 
        'num_guests', 
        'customer_name', 
        'customer_email', 
        'customer_phone'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
