<?php

namespace Database\Factories;

use App\Enums\FuelType;
use App\Enums\TransmissionType;
use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'vehicle_category_id' => VehicleCategory::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'brand' => fake()->randomElement(['Toyota', 'Daihatsu', 'Honda', 'Suzuki']),
            'model' => fake()->word(),
            'year' => fake()->numberBetween(2015, 2026),
            'transmission' => fake()->randomElement(TransmissionType::cases()),
            'fuel_type' => FuelType::Gasoline,
            'passenger_capacity' => fake()->numberBetween(2, 8),
            'description' => fake()->paragraph(),
            'status' => VehicleStatus::Available,
            'is_featured' => false,
            'is_published' => true,
            'sort_order' => fake()->numberBetween(0, 50),
            'meta_title' => null,
            'meta_description' => null,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['is_published' => false]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    public function status(VehicleStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
