<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'origin',
        'destination',
        'distance',
        'estimated_duration',
        'base_fare',
        'waypoints',
        'is_active'
    ];

    protected $casts = [
        'waypoints' => 'array',
        'is_active' => 'boolean',
        'distance' => 'decimal:2',
        'base_fare' => 'decimal:2'
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
