<?php

namespace Database\Factories;

use App\Enums\RentalServiceType;
use App\Models\Vehicle;
use App\Models\VehicleRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleRate>
 */
class VehicleRateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'service_type' => RentalServiceType::SelfDrive,
            'price_per_day' => fake()->numberBetween(10, 100) * 10000,
            'includes_driver' => false,
            'includes_fuel' => false,
            'notes' => null,
            'is_active' => true,
        ];
    }

    public function withDriver(): static
    {
        return $this->state(fn () => [
            'service_type' => RentalServiceType::WithDriver,
            'includes_driver' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
