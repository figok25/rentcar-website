<?php

namespace Database\Factories;

use App\Models\Tour;
use App\Models\TourImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TourImage>
 */
class TourImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tour_id' => Tour::factory(),
            'image_path' => 'tours/'.fake()->uuid().'.jpg',
            'alt_text' => fake()->sentence(3),
            'is_primary' => false,
            'sort_order' => 0,
        ];
    }
}
