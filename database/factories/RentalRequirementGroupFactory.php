<?php

namespace Database\Factories;

use App\Models\RentalRequirementGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RentalRequirementGroup>
 */
class RentalRequirementGroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vehicle_category_id' => null,
            'name' => fake()->unique()->words(2, true),
            'description' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
