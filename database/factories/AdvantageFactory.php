<?php

namespace Database\Factories;

use App\Models\Advantage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Advantage>
 */
class AdvantageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'icon' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
