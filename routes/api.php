<?php

use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;

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
