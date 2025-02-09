<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['room_type', 'capacity'];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

}
