<?php

namespace Database\Factories;

use App\Models\Route;
use Illuminate\Database\Eloquent\Factories\Factory;

class RouteFactory extends Factory
{
    protected $model = Route::class;

    public function definition()
    {
        return [
            'name' => $this->faker->city . ' to ' . $this->faker->city,
            'origin' => $this->faker->city,
            'destination' => $this->faker->city,
            'distance' => $this->faker->randomFloat(2, 5, 500),
            'estimated_duration' => $this->faker->numberBetween(15, 480), // in minutes
            'base_fare' => $this->faker->randomFloat(2, 100, 5000),
            'waypoints' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
