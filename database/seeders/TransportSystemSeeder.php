<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Route;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransportSystemSeeder extends Seeder
{
    public function run()
    {
        // Create a test user
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // Create vehicles
        $vehicles = [
            [
                'make' => 'Toyota',
                'model' => 'Hiace',
                'year' => 2023,
                'registration_number' => 'KDA 123A',
                'type' => 'minibus',
                'status' => 'active',
                'capacity' => 14,
                'fuel_efficiency' => 12.5,
            ],
            [
                'make' => 'Nissan',
                'model' => 'Urvan',
                'year' => 2022,
                'registration_number' => 'KDA 456B',
                'type' => 'minibus',
                'status' => 'active',
                'capacity' => 12,
                'fuel_efficiency' => 11.8,
            ]
        ];

        foreach ($vehicles as $vehicleData) {
            Vehicle::firstOrCreate(
                ['registration_number' => $vehicleData['registration_number']],
                $vehicleData
            );
        }

        // Create drivers
        $drivers = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '254712345678',
                'license_number' => 'DL12345678',
                'license_expiry' => now()->addYears(3)->format('Y-m-d'),
                'status' => 'active',
                'rating' => 4.5,
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '254723456789',
                'license_number' => 'DL23456789',
                'license_expiry' => now()->addYears(2)->format('Y-m-d'),
                'status' => 'active',
                'rating' => 4.8,
            ]
        ];

        foreach ($drivers as $driverData) {
            Driver::firstOrCreate(
                ['license_number' => $driverData['license_number']],
                $driverData
            );
        }

        // Create routes
        $routes = [
            [
                'name' => 'Nairobi CBD to Westlands',
                'origin' => 'Nairobi CBD',
                'destination' => 'Westlands',
                'distance' => 7.5,
                'estimated_duration' => 30,
                'base_fare' => 200,
                'waypoints' => ['Museum Hill', 'Westlands Roundabout'],
                'is_active' => true,
            ],
            [
                'name' => 'Nairobi to Thika',
                'origin' => 'Nairobi CBD',
                'destination' => 'Thika Town',
                'distance' => 42.5,
                'estimated_duration' => 90,
                'base_fare' => 150,
                'waypoints' => ['Ruiru', 'Juja', 'Mangu'],
                'is_active' => true,
            ]
        ];

        foreach ($routes as $routeData) {
            Route::firstOrCreate(
                ['name' => $routeData['name']],
                $routeData
            );
        }
    }
}
