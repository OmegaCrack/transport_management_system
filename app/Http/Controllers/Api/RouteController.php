<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RouteController extends Controller
{
    public function index(): JsonResponse
    {
        $routes = Route::with('bookings')->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $routes
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'origin' => 'required|string',
            'destination' => 'required|string',
            'distance' => 'required|numeric|min:0',
            'estimated_duration' => 'required|integer|min:1',
            'base_fare' => 'required|numeric|min:0',
            'waypoints' => 'nullable|array'
        ]);

        $route = Route::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Route created successfully',
            'data' => $route
        ], 201);
    }

    public function show(Route $route): JsonResponse
    {
        $route->load('bookings');
        
        return response()->json([
            'success' => true,
            'data' => $route
        ]);
    }

    public function update(Request $request, Route $route): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'origin' => 'sometimes|string',
            'destination' => 'sometimes|string',
            'distance' => 'sometimes|numeric|min:0',
            'estimated_duration' => 'sometimes|integer|min:1',
            'base_fare' => 'sometimes|numeric|min:0',
            'waypoints' => 'nullable|array',
            'is_active' => 'sometimes|boolean'
        ]);

        $route->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Route updated successfully',
            'data' => $route
        ]);
    }

    public function destroy(Route $route): JsonResponse
    {
        $route->delete();

        return response()->json([
            'success' => true,
            'message' => 'Route deleted successfully'
        ]);
    }
}
