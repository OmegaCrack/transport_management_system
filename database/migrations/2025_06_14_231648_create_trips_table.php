<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->constrained()->onDelete('cascade');
            $table->datetime('actual_departure');
            $table->datetime('actual_arrival')->nullable();
            $table->decimal('fuel_consumed', 8, 2)->nullable();
            $table->decimal('distance_covered', 8, 2)->nullable();
            $table->json('gps_coordinates')->nullable();
            $table->enum('status', ['started', 'in_progress', 'completed', 'cancelled'])->default('started');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
