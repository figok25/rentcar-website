<?php

namespace Database\Factories;

use App\Models\AdditionalFee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdditionalFee>
 */
class AdditionalFeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'amount' => fake()->numberBetween(1, 20) * 10000,
            'description' => fake()->sentence(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
