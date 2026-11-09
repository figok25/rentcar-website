<?php

namespace Database\Factories;

use App\Models\Motto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Motto>
 */
class MottoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
