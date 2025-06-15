<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_reference',
        'user_id',
        'route_id',
        'vehicle_id',
        'driver_id',
        'departure_time',
        'arrival_time',
        'passengers',
        'total_fare',
        'status',
        'payment_status',
        'notes'
    ];

    // Payment statuses
    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_FAILED = 'failed';
    public const PAYMENT_STATUS_REFUNDED = 'refunded';

    // Booking statuses
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'total_fare' => 'decimal:2',
        'passengers' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'payment_status' => self::PAYMENT_STATUS_PENDING,
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($booking) {
            $booking->booking_reference = 'TMS-' . strtoupper(Str::random(8));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function markAsPaid()
    {
        $this->update([
            'payment_status' => self::PAYMENT_STATUS_PAID,
            'status' => self::STATUS_CONFIRMED
        ]);
        return $this;
    }

    public function markAsFailed()
    {
        $this->update(['payment_status' => self::PAYMENT_STATUS_FAILED]);
        return $this;
    }

    public function markAsRefunded()
    {
        $this->update(['payment_status' => self::PAYMENT_STATUS_REFUNDED]);
        return $this;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public function isPendingPayment(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PENDING;
    }

    public function trip()
    {
        return $this->hasOne(Trip::class);
    }
}
