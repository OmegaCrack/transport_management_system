<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'vehicle_id',
        'driver_id',
        'actual_departure',
        'actual_arrival',
        'fuel_consumed',
        'distance_covered',
        'gps_coordinates',
        'status'
    ];

    protected $casts = [
        'actual_departure' => 'datetime',
        'actual_arrival' => 'datetime',
        'fuel_consumed' => 'decimal:2',
        'distance_covered' => 'decimal:2',
        'gps_coordinates' => 'array'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
