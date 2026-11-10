<?php

namespace Database\Factories;

use App\Models\SocialMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialMedia>
 */
class SocialMediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Instagram',
            'url' => 'https://instagram.com/'.fake()->userName(),
        ];
    }
}
