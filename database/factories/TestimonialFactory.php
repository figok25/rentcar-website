<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_display_name' => fake()->firstName(),
            'content' => fake()->paragraph(),
            'rating' => fake()->numberBetween(4, 5),
            'source_label' => null,
            'is_published' => true,
            'sort_order' => 0,
        ];
    }
}
