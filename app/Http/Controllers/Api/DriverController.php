<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $drivers = Driver::all();
        return response()->json($drivers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'license_number' => 'required|string|unique:drivers,license_number',
            'phone' => 'required|string|max:20',
            'status' => 'sometimes|in:available,on_trip,on_leave',
        ]);

        $driver = Driver::create($validated);
        return response()->json($driver, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Driver $driver): JsonResponse
    {
        return response()->json($driver);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Driver $driver): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'license_number' => 'sometimes|string|unique:drivers,license_number,' . $driver->id,
            'phone' => 'sometimes|string|max:20',
            'status' => 'sometimes|in:available,on_trip,on_leave',
        ]);

        $driver->update($validated);
        return response()->json($driver);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Driver $driver): JsonResponse
    {
        $driver->delete();
        return response()->json(null, 204);
    }
}
