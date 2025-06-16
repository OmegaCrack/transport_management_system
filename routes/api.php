<?php

use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Booking;
use App\Notifications\BookingConfirmedNotification;

// Test route for Africa's Talking SMS notification
Route::get('/test-sms', function () {
    // Create a test user with a phone number
    $user = new User([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+254700000000', // Replace with a valid test number
    ]);

    // Create a test booking
    $booking = new Booking([
        'id' => 1,
        'reference' => 'TEST' . time(),
        'status' => 'confirmed',
        'pickup_location' => 'Test Location',
        'dropoff_location' => 'Test Destination',
        'pickup_time' => now()->addHour(),
    ]);

    try {
        // Send the notification
        $user->notify(new BookingConfirmedNotification($booking));
        return response()->json(['message' => 'Test SMS notification sent successfully']);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to send SMS notification',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

Route::prefix('v1')->group(function () {
    // Vehicles
    Route::apiResource('vehicles', VehicleController::class);
    Route::get('vehicles/available', [VehicleController::class, 'available']);

    // Drivers
    Route::apiResource('drivers', DriverController::class);

    // Routes
    Route::apiResource('routes', RouteController::class);

    // Bookings
    Route::apiResource('bookings', BookingController::class);
    Route::patch('bookings/{booking}/cancel', [BookingController::class, 'cancel']);

    // Trips
    Route::get('trips', [TripController::class, 'index']);
    Route::post('trips/start', [TripController::class, 'start']);
    Route::patch('trips/{trip}/location', [TripController::class, 'updateLocation']);
    Route::patch('trips/{trip}/complete', [TripController::class, 'complete']);

    // Payments
    Route::apiResource('payments', PaymentController::class)->only(['index', 'show']);
    Route::post('payments/create-intent', [PaymentController::class, 'createPaymentIntent']);
    Route::post('payments/confirm', [PaymentController::class, 'confirmPayment']);
});
