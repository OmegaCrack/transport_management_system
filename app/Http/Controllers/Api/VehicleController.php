<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VehicleController extends Controller
{
    public function index(): JsonResponse
    {
        $vehicles = Vehicle::with(['bookings', 'trips'])->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $vehicles
        ]);
    }
    
    public function available()
    {
        $vehicles = Vehicle::where('status', 'available')->get();
        
        return response()->json([
            'success' => true,
            'data' => $vehicles
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'license_plate' => 'required|string|unique:vehicles|max:20',
            'status' => 'required|in:available,inactive,scheduled,maintenance',
            'capacity' => 'required|integer|min:1',
            'color' => 'nullable|string|max:50',
            'mileage' => 'nullable|integer|min:0',
            'last_service_date' => 'nullable|date',
            'next_service_date' => 'nullable|date|after:last_service_date'
        ]);

        $vehicle = Vehicle::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle created successfully',
            'data' => $vehicle
        ], 201);
    }

    public function show(Vehicle $vehicle): JsonResponse
    {
        $vehicle->load(['bookings', 'trips']);
        
        return response()->json([
            'success' => true,
            'data' => $vehicle
        ]);
    }

    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $validated = $request->validate([
            'make' => 'sometimes|string|max:255',
            'model' => 'sometimes|string|max:255',
            'year' => 'sometimes|integer|min:1900|max:' . (date('Y') + 1),
            'license_plate' => 'sometimes|string|unique:vehicles,license_plate,' . $vehicle->id,
            'status' => 'sometimes|in:available,inactive,scheduled,maintenance',
            'capacity' => 'sometimes|integer|min:1',
            'color' => 'nullable|string|max:50',
            'mileage' => 'nullable|integer|min:0',
            'last_service_date' => 'nullable|date',
            'next_service_date' => 'nullable|date|after:last_service_date'
        ]);

        $vehicle->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle updated successfully',
            'data' => $vehicle
        ]);
    }

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $vehicle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle deleted successfully'
        ]);
    }
}
