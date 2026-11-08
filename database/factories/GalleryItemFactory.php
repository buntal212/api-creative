<?php

namespace Database\Factories;

use App\Models\GalleryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryItem>
 */
class GalleryItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'path' => 'gallery/'.fake()->uuid().'.jpg',
            'alt_text' => fake()->sentence(4),
            'sort_order' => fake()->numberBetween(1, 100),
            'is_published' => true,
        ];
    }
}
