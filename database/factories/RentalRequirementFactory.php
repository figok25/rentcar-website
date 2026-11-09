<?php

namespace Database\Factories;

use App\Models\RentalRequirement;
use App\Models\RentalRequirementGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RentalRequirement>
 */
class RentalRequirementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rental_requirement_group_id' => RentalRequirementGroup::factory(),
            'requirement_text' => fake()->sentence(),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
