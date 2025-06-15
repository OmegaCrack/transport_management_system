<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_number',
        'make',
        'model',
        'year',
        'type',
        'capacity',
        'status',
        'fuel_efficiency'
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
