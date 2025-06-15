<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TripController extends Controller
{
    public function index(): JsonResponse
    {
        $trips = Trip::with(['booking', 'vehicle', 'driver'])->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $trips
        ]);
    }

    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id'
        ]);

        $booking = Booking::findOrFail($request->booking_id);
        
        if ($booking->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Booking must be confirmed to start trip'
            ], 400);
        }

        $trip = Trip::create([
            'booking_id' => $request->booking_id,
            'vehicle_id' => $request->vehicle_id,
            'driver_id' => $request->driver_id,
            'actual_departure' => now(),
            'status' => 'started'
        ]);

        $booking->update(['status' => 'in_progress']);

        return response()->json([
            'success' => true,
            'message' => 'Trip started successfully',
            'data' => $trip->load(['booking', 'vehicle', 'driver'])
        ], 201);
    }

    public function updateLocation(Request $request, Trip $trip): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        $coordinates = $trip->gps_coordinates ?? [];
        $coordinates[] = [
            'lat' => $request->latitude,
            'lng' => $request->longitude,
            'timestamp' => now()->toISOString()
        ];

        $trip->update([
            'gps_coordinates' => $coordinates,
            'status' => 'in_progress'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully'
        ]);
    }

    public function complete(Request $request, Trip $trip): JsonResponse
    {
        $request->validate([
            'fuel_consumed' => 'nullable|numeric|min:0',
            'distance_covered' => 'nullable|numeric|min:0'
        ]);

        $trip->update([
            'actual_arrival' => now(),
            'fuel_consumed' => $request->fuel_consumed,
            'distance_covered' => $request->distance_covered,
            'status' => 'completed'
        ]);

        $trip->booking->update(['status' => 'completed']);

        return response()->json([
            'success' => true,
            'message' => 'Trip completed successfully',
            'data' => $trip->load(['booking', 'vehicle', 'driver'])
        ]);
    }
}
