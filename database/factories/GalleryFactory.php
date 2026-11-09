<?php

namespace Database\Factories;

use App\Models\Gallery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gallery>
 */
class GalleryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'image_path' => 'galleries/'.fake()->uuid().'.jpg',
            'alt_text' => fake()->sentence(3),
            'category' => null,
            'sort_order' => 0,
            'is_published' => true,
        ];
    }
}
